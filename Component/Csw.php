<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Doctrine\ORM\EntityManagerInterface;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\Parameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostDomParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\TransactionParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Exception\GetCapabilitiesNotFoundException;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpKernel\KernelInterface;
use WhereGroup\CoreBundle\Component\Logger;
use WhereGroup\CoreBundle\Component\MetadataInterface;
use WhereGroup\CoreBundle\Component\Search\Expression;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;
use WhereGroup\CoreBundle\Component\Search\JsonFilterReader;
use WhereGroup\CoreBundle\Component\Search\Search;
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
     * @var \Doctrine\Common\Persistence\ObjectRepository|null|\Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository
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
            $this->metadataSearch
        );
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function count()
    {
        return (int)$this->repo->count();
    }

    /**
     * @return array|\WhereGroup\CoreBundle\Entity\Source[]
     */
    public function all()
    {
        return $this->repo->findAll();
    }

    /**
     * @param $slug
     * @param $source
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlugAndSource($slug, $source)
    {
        return $this->repo->findOneBySlugAndSource($slug, $source);
    }

    /**
     * @param $entity
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save($entity)
    {
        $this->repo->save($entity);

        return $this;
    }

    /**
     * @param $entity
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
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
     */
    public function readPostParameter($content)
    {
        return new PostDomParameter($content);
    }

    /**
     * @param $content
     * @return TransactionParameter
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
     * @throws \Twig\Error\Error
     */
    public function getCapabilities(Parameter $parameter, CswEntity $cswConfig, $url, $urlTransaction)
    {
        $operation = $parameter->initOperation(new GetCapabilities($cswConfig, $url, $urlTransaction));
        $operation->validateParameter();

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:getcapabilities_response.xml.twig',
            array(
                'getcap' => $operation,
            )
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws CswException
     * @throws \Twig\Error\Error
     */
    public function describeRecord(Parameter $parameter, CswEntity $cswConfig)
    {
        $operation = $parameter->initOperation(new DescribeRecord($cswConfig));
        $operation->validateParameter();

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:describerecord.xml.twig',
            array(
                'descrec' => $operation,
            )
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws \Exception
     */
    public function getRecordById(Parameter $parameter, CswEntity $cswConfig)
    {
        /* @var ExprHandler $exprHandler */
        $exprHandler = $this->metadataSearch->createExpression();
        $operation = $parameter->initOperation(new GetRecordById($cswConfig, $exprHandler));
        $operation->validateParameter();

        /* @var Expression $cswAndGetRecordByIdExpr */
        $cswAndGetRecordByIdExpr = $this->mergeExpression(
            $exprHandler,
            $this->getExpressionForCsw($cswConfig, $exprHandler),
            $operation->getConstraint()
        );

        $pluginLocation = $this->getProfileLocations($cswConfig->getProfileMapping());
        $templateName = self::getTemplateForElementSetName($operation->getElementSetName());
        $result = $this->metadataSearch
            ->setSource($cswConfig->getSource())
            ->setExpression($cswAndGetRecordByIdExpr)
            ->find();
        return $this->templating->render(
            'CatalogueServiceBundle:CSW:recordbyid_response.xml.twig',
            array(
                'getredcordbyid' => $operation,
                'pluginLocation' => $pluginLocation,
                'templateName' => $templateName,
                'records' => $result['rows'],
            )
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws CswException
     * @throws \Twig\Error\Error
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function getRecords(Parameter $parameter, CswEntity $cswConfig)
    {
        /* @var ExprHandler $exprHandler */
        $exprHandler = $this->metadataSearch->createExpression();
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
        $result = $this->metadataSearch->find();

        $matched = $result['paging']->count;
        $records = $result['rows'];
        $time = new \DateTime();
        $next = $offset + count($records) + 1;
        $pluginLocation = $this->getProfileLocations($cswConfig->getProfileMapping());
        $templateName = self::getTemplateForElementSetName($operation->getElementSetName());

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:records_response.xml.twig',
            array(
                'getrecords' => $operation,
                'pluginLocation' => $pluginLocation,
                'templateName' => $templateName,
                'timestamp' => $time->format('Y-m-d\TH:i:s'),
                'matched' => $matched,
                'records' => $records,
                'nextrecord' => $next > $matched ? 0 : $next,
            )
        );
    }

    /**
     * @param TransactionParameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     * @throws CswException
     * @throws \Exception
     * @throws \Twig\Error\Error
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function transaction(TransactionParameter $parameter, CswEntity $cswConfig)
    {
        $operationName = $parameter->getOperationName();
        if ($operationName === 'GetCapabilities') {
            throw new GetCapabilitiesNotFoundException();
        } elseif ($operationName !== 'Transaction') {
            throw new CswException('request', CswException::OPERATIONNOTSUPPORTED);
        }
        $operation = $parameter->initOperation(new Transaction($cswConfig));

        while (($action = $parameter->nextAction($operation, $this->metadataSearch->createExpression()))) {
            switch (($atype = $action->getType())) {
                case Transaction::INSERT:
                    if (!$cswConfig->getInsert()) {
                        throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
                    }
                    $inserted = $this->doInsert($cswConfig, $action, $parameter);
                    $operation->addInserted($inserted);
                    break;
                case Transaction::UPDATE:
                    if (!$cswConfig->getUpdate()) {
                        throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
                    }
                    $updated = $this->doUpdate($cswConfig, $action, $parameter);
                    $operation->addUpdated($updated);
                    break;
                case Transaction::DELETE:
                    if (!$cswConfig->getDelete()) {
                        throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
                    }
                    $deleted = $this->doDelete($cswConfig, $action, $parameter);
                    $operation->addDeleted($deleted);
                    break;
                default:
                    throw new CswException($atype, CswException::OPERATIONNOTSUPPORTED);
            }
        }

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:transaction_response.xml.twig',
            array(
                'ta' => $operation,
            )
        );
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     * @throws CswException
     */
    public function doInsert(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
        $inserted = 0;
        /* list of metadates- no filter */
        foreach ($action->getItems() as $mdMetadata) {
            $hierarchyLevel = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdMetadata);
            $profiles = $cswConfig->getProfileMapping();
            if (isset($profiles[$hierarchyLevel])) {
                $profile = $profiles[$hierarchyLevel];
                $source = $cswConfig->getSource();
                $username = $cswConfig->getUsername();
                $public = true;
                $xml = self::elementToString($mdMetadata);

                $p = $this->metadata->xmlToObject($xml, $profile);

                if ($this->metadata->exists($p['fileIdentifier'])) {
                    throw new CswException('fileIdentifier', CswException::INVALIDPARAMETERVALUE);
                }

                $this->metadata->saveObject($p, null, [
                    'source'   => $source,
                    'profile'  => $profile,
                    'username' => $username,
                    'public'   => $public
                ]);

                $inserted++;
            } else {
                $this->log($cswConfig, 'warning', 'insert', '', 'Type: $hl ist nicht unterstützt');
            }
        }

        return $inserted;
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function doUpdate(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
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

            $this->metadataSearch
                ->setSource($cswConfig->getSource());
            if ($cswAndDeleteExpr) {
                $this->metadataSearch->setExpression($cswAndDeleteExpr);
            }
            $this->metadataSearch->find();
            $records = $this->metadataSearch->getResult();
            /* datarow to update */
            foreach ($records as $record) {
                $existing = json_decode($record['object'], true);
                foreach ($action->getItems() as $mdMetadata) {
                    $hl = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdMetadata);
                    $profiles = $cswConfig->getProfileMapping();
                    if (isset($profiles[$hl])) {
                        $profile = $profiles[$hl];
                        $source = $cswConfig->getSource();
                        $username = $cswConfig->getUsername();
                        $public = true;
                        $xml = self::elementToString($mdMetadata);
                        /* data for datarow to update */
                        $new = $this->metadata->xmlToObject($xml, $profile);

                        $id = !empty($existing['_id']) ? $existing['_id'] : null;
                        $id = is_null($id) && !empty($existing['_uuid']) ? $existing['_uuid'] : $id;

                        $this->metadata->saveObject($new, $id, [
                            'source'   => $source,
                            'profile'  => $profile,
                            'username' => $username,
                            'public'   => $public
                        ]);

                        $updated++;
                    } else {
                        $this->log($cswConfig, 'warning', 'update', '', 'Type: $hl ist nicht unterstützt');
                    }
                    break;
                }
            }
        }

        return $updated;
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     * @throws CswException
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function doDelete(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
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

            $this->metadataSearch
                ->setSource($cswConfig->getSource());
            if ($cswAndDeleteExpr) {
                $this->metadataSearch->setExpression($cswAndDeleteExpr);
            }
            $this->metadataSearch->find();

            $records = $this->metadataSearch->getResult();
            foreach ($records as $record) {
                $p = json_decode($record['object'], true);
                $item = $this->metadata->getById($p['_uuid']);
                $this->metadata->deleteById($item->getId());
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * @param CswEntity $cswConfig
     * @param ExprHandler $exprHandler
     * @return null|Expression
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    private function getExpressionForCsw(CswEntity $cswConfig, ExprHandler $exprHandler)
    {
        $cswExpr = JsonFilterReader::readWithAlias($cswConfig->getFilter(), $exprHandler);

        return $cswExpr;
    }

    /**
     * @param ExprHandler $exprHandler
     * @param Expression|null $exprA
     * @param Expression|null $exprB
     * @return null|Expression
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
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
                $exprHandler->andx(
                    array(
                        $exprA->getExpression(),
                        $exprB->getExpression(),
                    )
                ),
                array_merge($exprA->getParameters(), $exprB->getParameters())
            );
        }
    }

    /**
     * @param array $profileMapping
     * @return array
     */
    private function getProfileLocations(array $profileMapping)
    {
        $pluginLocation = array();
        foreach ($profileMapping as $hierarchyLevel => $profile) {
            if (!isset($pluginLocation[$profile])) {
                $plugin = $this->plugin->getPlugin($profile);
                $pluginLocation[$profile] = array(
                    'sf' => '@'.$plugin['class_name'].':Export:',
                    'full' => $this->kernel->locateResource('@'.$plugin['class_name'].'/Resources/views/Export/'),
                );
            }
        }

        return $pluginLocation;
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
            ->setType($type)//('warning')
            ->setCategory('application')
            ->setSubcategory('csw')
            ->setOperation($operation)//('insert')
            ->setSource($entity->getSource())//('')
            ->setIdentifier($identifier)//('')
            ->setMessage($message)//('test')
            ->setUser($entity->getUsername());//('');
        $this->logger->set($log);
    }


    private static function elementToString(\DOMElement $element)
    {
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($element, true));
        return $doc->saveXML();
    }


    /**
     * @param $elementSetName
     * @return string
     * @throws CswException
     */
    private static function getTemplateForElementSetName($elementSetName)
    {
        switch ($elementSetName) {
            case 'full':
                return 'metadata.xml.twig';
            case 'summary':
                return 'metadata.xml.twig';
            case 'brief':
                return 'metadata.xml.twig';
            default:
                throw new CswException('elementSetName', CswException::NOAPPLICABLECODE);
        }
    }
}
