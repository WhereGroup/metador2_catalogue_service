<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use DateTime;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use DOMDocument;
use DOMElement;
use Exception;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\Parameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostDomParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\TransactionParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Exception\GetCapabilitiesNotFoundException;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Error\Error;
use WhereGroup\CoreBundle\Component\Exceptions\MetadataException;
use WhereGroup\CoreBundle\Component\Logger;
use WhereGroup\CoreBundle\Component\MetadataInterface;
use WhereGroup\CoreBundle\Component\Search\Expression;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;
use WhereGroup\CoreBundle\Component\Search\JsonFilterReader;
use WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException;
use WhereGroup\CoreBundle\Component\Search\Search;
use WhereGroup\CoreBundle\Component\Utils\ArrayParser;
use WhereGroup\CoreBundle\Component\Utils\Stopwatch;
use WhereGroup\CoreBundle\Entity\Source;
use WhereGroup\PluginBundle\Component\Plugin;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 * @author  Paul Schmidt <panadium@gmx.de>
 */
class Csw
{
    const ENTITY = "CatalogueServiceBundle:Csw";
    /**
     * @var ObjectRepository|null|CswRepository
     */
    private $repo = null;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var
     */
    private $templating;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var Search
     */
    private $metadataSearch;

    /**
     * @var MetadataInterface
     */
    private $metadata;

    /**
     * Csw constructor.
     * @param KernelInterface $kernel
     * @param EntityManagerInterface $em
     * @param TwigEngine $templating
     * @param Logger $logger
     * @param Plugin $plugin
     * @param Search $metadataSearch
     * @param MetadataInterface $metadata
     */
    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $em,
        TwigEngine $templating,
        Logger $logger,
        Plugin $plugin,
        Search $metadataSearch,
        MetadataInterface $metadata
    ) {
        $this->kernel = $kernel;
        $this->repo = $em->getRepository(self::ENTITY);
        $this->templating = $templating;
        $this->logger = $logger;
        $this->plugin = $plugin;
        $this->metadataSearch = $metadataSearch;
        $this->metadata = $metadata;
    }

    /**
     * Csw destructor.
     */
    public function __destruct()
    {
        unset(
            $this->kernel,
            $this->repo,
            $this->logger,
            $this->plugin,
            $this->metadataSearch,
            $this->stopwatch
        );
    }

    /**
     * @return int
     * @throws NonUniqueResultException
     */
    public function count()
    {
        return (int)$this->repo->countAll();
    }

    /**
     * @return array|Source[]
     */
    public function all()
    {
        return $this->repo->findAll();
    }

    /**
     * @param $slug
     * @param $source
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneBySlugAndSource($slug, $source)
    {
        return $this->repo->findOneBySlugAndSource($slug, $source);
    }

    /**
     * @param $entity
     * @return $this
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save($entity)
    {
        $this->repo->save($entity);

        return $this;
    }

    /**
     * @param $entity
     * @return $this
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($entity)
    {
        $this->repo->remove($entity);

        return $this;
    }

    /**
     * @param array $getParameters
     * @return GetParameter
     */
    public function readGetParameter(array $getParameters)
    {
        return new GetParameter($getParameters);
    }

    /**
     * @param $content
     * @return PostDomParameter
     * @throws CswException
     */
    public function readPostParameter($content)
    {
        return new PostDomParameter($content);
    }

    /**
     * @param $content
     * @return TransactionParameter
     * @throws CswException
     */
    public function readTransactionParameter($content)
    {
        return new TransactionParameter($content);
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @param $url
     * @param $urlTransaction
     * @return string
     * @throws CswException
     * @throws Error
     */
    public function getCapabilities(Parameter $parameter, CswEntity $cswConfig, $url, $urlTransaction)
    {
        $operation = $parameter->initOperation(new GetCapabilities($cswConfig, $url, $urlTransaction));
        $operation->validateParameter();

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:getcapabilities_response.xml.twig',
            [
                'getcap' => $operation,
            ]
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws CswException
     * @throws Error
     */
    public function describeRecord(Parameter $parameter, CswEntity $cswConfig)
    {
        $operation = $parameter->initOperation(new DescribeRecord($cswConfig));
        $operation->validateParameter();

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:describerecord.xml.twig',
            ['descrec' => $operation]
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws Exception
     */
    public function getRecordById(Parameter $parameter, CswEntity $cswConfig)
    {
        /* @var ExprHandler $exprHandler */
        $exprHandler = $this->metadataSearch->createExpression();

        /**
         * @var GetRecordById $operation
         */
        $operation = $parameter->initOperation(new GetRecordById($cswConfig, $exprHandler));
        $operation->validateParameter();

        /* @var Expression $cswAndGetRecordByIdExpr */
        $cswAndGetRecordByIdExpr = $this->mergeExpression(
            $exprHandler,
            $this->getExpressionForCsw($cswConfig, $exprHandler),
            $operation->getConstraint()
        );

        // Prepare result for full, summary and brief metadata.
        $result = $this->metadataSearch
            ->setSource($cswConfig->getSource())
            ->setExpression($cswAndGetRecordByIdExpr)
            ->find();

        $result['rows'] = $this->prepareResult($result['rows'], $operation->getElementSetName());
        // $operation->getOutputFormat(); // one of $this->supportedOutputFormats = $this->supportedOutputFormats = ["application/xml", "text/html", "application/pdf"];
        return $this->templating->render(
            'CatalogueServiceBundle:CSW:recordbyid_response.xml.twig',
            [
                'getredcordbyid' => $operation,
                'templates'      => $this->getProfileLocations(),
                'records'        => $result['rows'],
            ]
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws CswException
     * @throws Error
     * @throws PropertyNameNotFoundException
     * @throws Exception
     */
    public function getRecords(Parameter $parameter, CswEntity $cswConfig)
    {
        /* @var ExprHandler $exprHandler */
        $exprHandler = $this->metadataSearch->createExpression();

        /**
         * @var GetRecords $operation
         */
        $operation = $parameter->initOperation(new GetRecords($cswConfig, $exprHandler));
        $operation->validateParameter();

        /* @var Expression $cswAndGetRecordsExpr */
        $cswAndGetRecordsExpr = $this->mergeExpression(
            $exprHandler,
            $this->getExpressionForCsw($cswConfig, $exprHandler),
            $operation->getConstraint()
        );

        $offset = $operation->getStartPosition() - 1;
        $this->metadataSearch
            ->setHits($operation->getMaxRecords())
            ->setOffset($offset)
            ->setSource($cswConfig->getSource());
        if ($cswAndGetRecordsExpr) {
            $this->metadataSearch->setExpression($cswAndGetRecordsExpr);
        }

        // Prepare result for full, summary and brief metadata.
        $result  = $this->metadataSearch->find();
        $records = $this->prepareResult($result['rows'], $operation->getElementSetName());
        $matched = $result['paging']->count;
        $next = $offset + count($records) + 1;
        // $operation->getOutputFormat(); // one of $this->supportedOutputFormats = $this->supportedOutputFormats = ["application/xml", "text/html", "application/pdf"];
        return $this->templating->render(
            'CatalogueServiceBundle:CSW:records_response.xml.twig',
            [
                'getrecords' => $operation,
                'templates'  => $this->getProfileLocations(),
                'timestamp'  => (new DateTime())->format('Y-m-d\TH:i:s'),
                'matched'    => $matched,
                'records'    => $records,
                'nextrecord' => $next > $matched ? 0 : $next,
            ]
        );
    }

    /**
     * @param array $rows
     * @param string $elementSetName
     * @return array
     */
    private function prepareResult(array $rows, $elementSetName = 'full')
    {
        if ($elementSetName != 'summary' && $elementSetName != 'brief') {
            return $rows;
        }

        $newResult = [];

        foreach ($rows as $row) {
            if (!isset($row['object'])) {
                continue;
            }

            $p = json_decode($row['object'], true);

            // brief
            $newObject['object'] = [
                'fileIdentifier' => ArrayParser::get($p, 'fileIdentifier', ''),
                'hierarchyLevel' => ArrayParser::get($p, 'hierarchyLevel', ''),
                'title'          => ArrayParser::get($p, 'title', ''),
                'bbox'           => ArrayParser::get($p, 'bbox', []),
            ];

            $this->metadata->mergeSystemInformations($p, $newObject['object']);

            if ($elementSetName === 'brief') {
                $newObject['object'] = json_encode($newObject['object']);
                $newResult[] = $newObject;
                continue;
            }

            // summary
            $newObject['object']['dateStamp'] = ArrayParser::get($p, 'dateStamp', '');
            $newObject['object']['abstract'] = ArrayParser::get($p, 'abstract', '');

            $newObject['object'] = json_encode($newObject['object']);
            $newResult[] = $newObject;
        }

        return $newResult;
    }

    /**
     * @param TransactionParameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws CswException
     * @throws Exception
     * @throws Error
     * @throws PropertyNameNotFoundException
     */
    public function transaction(TransactionParameter $parameter, CswEntity $cswConfig)
    {
        $operationName = $parameter->getOperationName();

        if ($operationName === 'GetCapabilities') {
            throw new GetCapabilitiesNotFoundException();
        } elseif ($operationName !== 'Transaction') {
            throw new CswException('request', CswException::OPERATIONNOTSUPPORTED);
        }

        /** @var Transaction $operation */
        $operation = $parameter->initOperation(new Transaction($cswConfig));

        while (($action = $parameter->nextAction($operation, $this->metadataSearch->createExpression()))) {
            switch (($atype = $action->getType())) {
                case Transaction::INSERT:
                    if (!$cswConfig->getInsert()) {
                        throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
                    }
                    $operation->addInserted($this->doInsert($cswConfig, $action, $parameter));
                    break;
                case Transaction::UPDATE:
                    if (!$cswConfig->getUpdate()) {
                        throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
                    }
                    $operation->addUpdated($this->doUpdate($cswConfig, $action, $parameter));
                    break;
                case Transaction::DELETE:
                    if (!$cswConfig->getDelete()) {
                        throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
                    }
                    $operation->addDeleted($this->doDelete($cswConfig, $action, $parameter));
                    break;
                default:
                    throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
            }
        }

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:transaction_response.xml.twig',
            ['ta' => $operation]
        );
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     * @throws CswException
     * @throws Exception
     */
    public function doInsert(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
        gc_collect_cycles();
        $em = $this->metadata->getEntityManager();
        $em->clear();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $inserted = 0;

        /* list of metadates- no filter */
        foreach ($action->getItems() as $mdMetadata) {
            $hierarchyLevel = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdMetadata);
            $profiles = $cswConfig->getProfileMapping();

            if (isset($profiles[$hierarchyLevel])) {
                $p = $this->metadata->xmlToObject(self::elementToString($mdMetadata), $profiles[$hierarchyLevel]);

                if ($this->metadata->exists($p['fileIdentifier'])) {
                    throw new CswException('fileIdentifier', CswException::INVALIDPARAMETERVALUE);
                }

                $this->metadata->saveObject($p, null, [
                    'source'   => $cswConfig->getSource(),
                    'profile'  => $profiles[$hierarchyLevel],
                    'username' => $cswConfig->getUsername(),
                    'public'   => true
                ]);

                $inserted++;
                continue;
            }

            $this->log($cswConfig, 'warning', 'insert', '', 'Type: $hl ist nicht unterstützt');
        }

        return $inserted;
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     * @throws PropertyNameNotFoundException
     * @throws Exception
     */
    public function doUpdate(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
        $sw = new Stopwatch();
        gc_collect_cycles();
        $em = $this->metadata->getEntityManager();
        $em->clear();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $updated = 0;
        if ($action->getConstraint()) {
            /* @var ExprHandler $exprHandler */
            $exprHandler = $this->metadataSearch->createExpression();

            /* @var Expression $cswAndDeleteExpr */
            $cswAndDeleteExpr = $this->mergeExpression(
                $exprHandler,
                $this->getExpressionForCsw($cswConfig, $exprHandler),
                $action->getConstraint()
            );

            $this->metadataSearch->setSource($cswConfig->getSource());

            if ($cswAndDeleteExpr) {
                $this->metadataSearch->setExpression($cswAndDeleteExpr);
            }

            $records = $this->metadataSearch->find();

            if ($records['rows'] === false) {
                return $updated;
            }

            foreach ($records['rows'] as $record) {
                $existing = json_decode($record['object'], true);

                foreach ($action->getItems() as $mdMetadata) {
                    $hl = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdMetadata);
                    $profiles = $cswConfig->getProfileMapping();

                    if (isset($profiles[$hl])) {
                        /* data for datarow to update */
                        $new = $this->metadata->xmlToObject(self::elementToString($mdMetadata), $profiles[$hl]);
                        $id = !empty($existing['_uuid']) ? $existing['_uuid'] : null;

                        $this->metadata->saveObject($new, $id, [
                            'source'   => $cswConfig->getSource(),
                            'profile'  => $profiles[$hl],
                            'username' => $cswConfig->getUsername(),
                            'public'   => true
                        ]);

                        $updated++;
                        continue;
                    }

                    $this->log($cswConfig, 'warning', 'update', '', 'Type: $hl ist nicht unterstützt');

                    break;
                }

                unset($existing);
            }

            unset($records);
        }

        $sw->stop();

        $this->log($cswConfig, 'debug', 'update', '', 'Updated ' . $updated . ' metadata in '
            . $sw->duration() . ' seconds.');

        return $updated;
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     * @throws MetadataException
     * @throws PropertyNameNotFoundException
     */
    public function doDelete(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
        $em = $this->metadata->getEntityManager();
        $em->clear();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $deleted = 0;
        if ($action->getConstraint()) {
            /* @var ExprHandler $exprHandler */
            $exprHandler = $this->metadataSearch->createExpression();

            /* @var Expression $cswAndDeleteExpr */
            $cswAndDeleteExpr = $this->mergeExpression(
                $exprHandler,
                $this->getExpressionForCsw($cswConfig, $exprHandler),
                $action->getConstraint()
            );

            $this->metadataSearch->setSource($cswConfig->getSource());

            if ($cswAndDeleteExpr) {
                $this->metadataSearch->setExpression($cswAndDeleteExpr);
            }

            $records = $this->metadataSearch->find();

            if ($records['rows'] !== false) {
                foreach ($records['rows'] as $record) {
                    $p = json_decode($record['object'], true);
                    $item = $this->metadata->getById($p['_uuid']);
                    $this->metadata->deleteById($item->getId());
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * @param CswEntity $cswConfig
     * @param ExprHandler $exprHandler
     * @return null|Expression
     * @throws PropertyNameNotFoundException
     */
    private function getExpressionForCsw(CswEntity $cswConfig, ExprHandler $exprHandler)
    {
        $cswExpr = JsonFilterReader::read($cswConfig->getFilter(), $exprHandler);

        return $cswExpr;
    }

    /**
     * @param ExprHandler $exprHandler
     * @param Expression|null $exprA
     * @param Expression|null $exprB
     * @return null|Expression
     */
    private function mergeExpression(ExprHandler $exprHandler, Expression $exprA = null, Expression $exprB = null)
    {
        if ($exprA === null && $exprB === null) {
            return null;
        } elseif ($exprA === null) {
            return $exprB;
        } elseif ($exprB === null) {
            return $exprA;
        } else {
            return new Expression(
                $exprHandler->andx([$exprA->getExpression(), $exprB->getExpression()]),
                array_merge($exprA->getParameters(), $exprB->getParameters())
            );
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getProfileLocations()
    {
        $templates = [];
        $profiles = $this->plugin->getActiveProfiles();

        if (empty($profiles)) {
            return $templates;
        }

        foreach ($profiles as $key => $profile) {
            $templates[$key] = '@' . strstr($profile['class_name'], 'Bundle', true) . '/Export/metadata.xml.twig';
        }

        return $templates;
    }

    /**
     * @param CswEntity $entity
     * @param string $type
     * @param string $operation
     * @param string $identifier
     * @param string $message
     */
    private function log(CswEntity $entity, $type, $operation, $identifier, $message)
    {
        $log = $this->logger->newLog();
        $log
            ->setType($type)
            ->setCategory('application')
            ->setSubcategory('csw')
            ->setOperation($operation)
            ->setSource($entity->getSource())
            ->setIdentifier($identifier)
            ->setMessage($message)
            ->setUser($entity->getUsername());

        $this->logger->set($log);
    }

    /**
     * @param DOMElement $element
     * @return string
     */
    private static function elementToString(DOMElement $element)
    {
        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($element, true));
        return $doc->saveXML();
    }
}
