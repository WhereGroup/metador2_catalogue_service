<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Doctrine\ORM\EntityManagerInterface;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\Parameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostDomParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\TransactionParameter;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;
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

//    /**
//     * @var Search
//     */
    private $metadataSearch;

    /**
     * @var
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
     * @return mixed
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
     */
    public function findOneBySlugAndSource($slug, $source)
    {
        return $this->repo->findOneBySlugAndSource($slug, $source);
    }

    /**
     * @param $entity
     * @return $this
     */
    public function save($entity)
    {
        $this->repo->save($entity);

        return $this;
    }

    /**
     * @param $entity
     * @return $this
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
     * @param string $url
     * @param string $urlTransaction
     * @return string
     */
    public function getCapabilities(Parameter $parameter, CswEntity $cswConfig, $url, $urlTransaction)
    {
        $operation = new GetCapabilities($cswConfig, $url, $urlTransaction);
        $parameter->initOperation($operation);
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
     */
    public function describeRecord(Parameter $parameter, CswEntity $cswConfig)
    {
        $operation = new DescribeRecord($cswConfig);
        $parameter->initOperation($operation);
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
        $operation = new GetRecordById($cswConfig);
        $parameter->initOperation($operation);
        $operation->validateParameter();
        $searchParameters = array();
        /* @var Expression $expr */
        $expr = null;
        if (($defExpr = JsonFilterReader::read($cswConfig->getFilter(), $exprHandler))) {
            $searchParameters = $defExpr->getParameters();
            $expr = new Expression(
                $exprHandler->andx(
                    array(
                        $defExpr->getExpression(),
                        // add ids to expression
                        $exprHandler->in('uuid', $operation->getId(), $searchParameters),
                    )
                ),
                $searchParameters
            );
        } else {
            // add ids to expression
            $expr = new Expression($exprHandler->in('uuid', $operation->getId(), $searchParameters), $searchParameters);
        }
        $pluginLocation = $this->getProfileLocations($cswConfig->getProfileMapping());
        $templateName = self::getTemplateForElementSetName($operation->getElementSetName());
        $this->metadataSearch
            ->setPage(1)
            ->setHits(100)// set max count for GetRecordById ???
            ->setSource($cswConfig->getSource())
            ->setExpression($expr)
            ->find();

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:recordbyid_response.xml.twig',
            array(
                'getredcordbyid' => $operation,
                'pluginLocation' => $pluginLocation,
                'templateName' => $templateName,
                'records' => $this->metadataSearch->getResult(),
            )
        );
    }

    /**
     * @param Parameter $parameter
     * @param CswEntity $cswConfig
     * @return string
     */
    public function getRecords(Parameter $parameter, CswEntity $cswConfig)
    {
        /* @var ExprHandler $exprHandler */
        $exprHandler = $this->metadataSearch->createExpression();
        $getrecords = new GetRecords($cswConfig, $this->metadataSearch->createExpression());
        $parameter->initOperation($getrecords);
        $getrecords->validateParameter();

        $pluginLocation = $this->getProfileLocations($cswConfig->getProfileMapping());
        $templateName = self::getTemplateForElementSetName($getrecords->getElementSetName());
        $offset = $getrecords->getStartPosition() - 1;

        /* @var Expression $expr */
        $expr = null;
        if (($defExpr = JsonFilterReader::read($cswConfig->getFilter(), $exprHandler))) {
            if ($getrecords->getConstraint()) {
                $expr = new Expression(
                    $exprHandler->andx(
                        array(
                            $defExpr->getExpression(),
                            // add ids to expression
                            $getrecords->getConstraint()->getExpression(),
                        )
                    ),
                    array_merge($getrecords->getConstraint()->getParameters(), $defExpr->getParameters())
                );
            }
        } else {
            $expr = $getrecords->getConstraint();
        }
        $this->metadataSearch
            ->setPage(0)// use no page
            ->setHits($getrecords->getMaxRecords())
            ->setOffset($offset)
            ->setSource($cswConfig->getSource());
        if ($expr) {
            $this->metadataSearch->setExpression($expr);
        }
        $this->metadataSearch->find();

        $time = new \DateTime();
        $matched = $this->metadataSearch->getResultCount();
        $records = $this->metadataSearch->getResult();
        $next = $offset + count($records) + 1;

        return $this->templating->render(
            'CatalogueServiceBundle:CSW:records_response.xml.twig',
            array(
                'getrecords' => $getrecords,
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
     */
    public function transaction(TransactionParameter $parameter, CswEntity $cswConfig)
    {
        $operationName = $parameter->getOperationName();
        if ($operationName !== 'Transaction') {
            throw new CswException('request', CswException::OperationNotSupported);
        }
        $operation = new Transaction($cswConfig);
        $parameter->initOperation($operation);

        while (($action = $parameter->nextAction($operation, $this->metadataSearch->createExpression()))) {
            switch ($action->getType()) {
                case Transaction::INSERT:
                    $inserted = $this->doInsert($cswConfig, $action, $parameter);
                    $operation->addInserted($inserted);
                    break;
                case Transaction::UPDATE:
                    $updated = $this->doUpdate($cswConfig, $action, $parameter);
                    $operation->addUpdated($updated);
                    break;
                case Transaction::DELETE:
                    $deleted = $this->doDelete($cswConfig, $action, $parameter);
                    $operation->addDeleted($deleted);
                    break;
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
        foreach ($action->getItems() as $mdMetadata) {
            $hierarchyLevel = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdMetadata);
            $profiles = $cswConfig->getProfileMapping();
            if (isset($profiles[$hierarchyLevel])) {
                $profile = $profiles[$hierarchyLevel];
                $source = $cswConfig->getSource();
                $username = $cswConfig->getUsername();
                $public = true;
                $xml = $mdMetadata->ownerDocument->saveXML($mdMetadata);

                $p = $this->metadata->xmlToObject($xml, $profile);
                $this->metadata
                    ->updateObject($p, $source, $profile, $username, $public);

                if ($this->metadata->exists($p['_uuid'])) {
                    throw new CswException('fileIdentifier', CswException::InvalidParameterValue);
                }

                $this->metadata->saveObject($p);
                $inserted++;
            } else {
                $this->log($cswConfig, 'warning', 'insert', '', 'Type: $hl ist nicht unterstützt');
            }
        }

        return $inserted;
    }

    /**
     * @param CswEntity $cswConfig
     * @param TransactionOperation $operation
     * @param TransactionParameter $handler
     * @return int
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function doUpdate(CswEntity $cswConfig, TransactionOperation $action, TransactionParameter $handler)
    {
        $updated = 0;
        foreach ($action->getItems() as $mdMetadata) {
            $hl = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdMetadata);
            $profiles = $cswConfig->getProfileMapping();
            if (isset($profiles[$hl])) {
                $profile = $profiles[$hl];
                $source = $cswConfig->getSource();
                $username = $cswConfig->getUsername();
                $public = true;
                $xml = $mdMetadata->ownerDocument->saveXML($mdMetadata);

                $p = $this->metadata->xmlToObject($xml, $profile);
                $this->metadata
                    ->updateObject($p, $source, $profile, $username, $public);
                if (!$this->metadata->exists($p['_uuid'])) {
                    throw new CswException('fileIdentifier', CswException::InvalidParameterValue);
                }
                $this->metadata->saveObject($p);
                $updated++;
            } else {
                $this->log($cswConfig, 'warning', 'update', '', 'Type: $hl ist nicht unterstützt');
            }
        }

        return $updated;
    }

    /**
     * @param CswEntity $entity
     * @param TransactionOperation $action
     * @param TransactionParameter $handler
     * @return int
     */
    public function doDelete(CswEntity $entity, TransactionOperation $action, TransactionParameter $handler)
    {
        $deleted = 0;
        foreach ($action->getItems() as $item) {
            // TODO do delete
        }

        return $deleted;
    }
//
//    /**
//     * @param array $profileMapping
//     * @return array
//     */
//    private function getImportConfig(array $profileMapping, $hierarchyLevel)
//    {
//        if (isset($profileMapping[$hierarchyLevel])) {
//            $plugin = $this->plugin->getPlugin($profileMapping[$hierarchyLevel]);
////            $file = $this->kernel->locateResource(
////                '@'.$plugin['class_name'].'/Resources/views/Import/metadata.xml.json'
////            );
//            $file = $this->kernel->locateResource(
//                '@'.$plugin['class_name'].'/Resources/config/import.json'
//            );
//
//            return $file;
//        } else {
//            return null;
//        }
//    }
//
//    /**
//     * @param array $profileMapping
//     * @param ExprHandler $expression
//     * @return mixed|null
//     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
//     */
//    private function getProfileExpression(array $profileMapping, ExprHandler $expression)
//    {
//        $or = array();
//        foreach ($profileMapping as $hierarchyLevel => $profile) {
//            $or[] = $expression->andx(
//                array(
//                    $expression->eq('hierarchyLevel', $hierarchyLevel),
//                    $expression->eq('profile', $profile),
//                )
//            );
//        }
//        if (count($or) > 1) {
//            return $expression->orx($or);
//        } elseif (count($or) === 1) {
//            return $or[0];
//        } else {
//            return null;
//        }
//    }

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
                throw new CswException('elementSetName', CswException::NoApplicableCode);
        }
    }
}
