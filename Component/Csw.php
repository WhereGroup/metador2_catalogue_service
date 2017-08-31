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
use WhereGroup\CoreBundle\Component\Search\Expression;
use WhereGroup\CoreBundle\Component\Search\Search;
use WhereGroup\PluginBundle\Component\Plugin;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 * @author Paul Schmidt <panadium@gmx.de>
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
        Search $metadataSearch
    ) {
        $this->kernel = $kernel;
        $this->repo = $em->getRepository(self::ENTITY);
        $this->templating = $templating;
        $this->logger = $logger;
        $this->plugin = $plugin;
        $this->metadataSearch = $metadataSearch;
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
     * @return PostParameter
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
        $operation = new GetRecordById($cswConfig);
        $parameter->initOperation($operation);
        $operation->validateParameter();
        /* @var Expression $expression */
        $expression = $this->metadataSearch->createExpression();
        // add ids into expression
        $uuidExpression = $expression->in('uuid', $operation->getId());
        // add expression into Expression
        if (($profileExpression = $this->getProfileExpression($cswConfig->getProfileMapping(), $expression))) {
            $expression->setResultExpression($expression->andx(array($uuidExpression, $profileExpression)));
        } else {
            $expression->setResultExpression($uuidExpression);
        }

//        $test = $this->metadataSearch->getResult();
        $pluginLocation = $this->getProfileLocations($cswConfig->getProfileMapping());
        $templateName = self::getTemplateForElementSetName($operation->getElementSetName());
        $this->metadataSearch
            ->setPage(1)
            ->setHits(100)// set max count for GetRecordById ???
            ->setSource($cswConfig->getSource())
            ->setExpression($expression)
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
        /**
         * @var Expression $expression
         */
        $expression = $this->metadataSearch->createExpression();
        $operation = new GetRecords($cswConfig, $expression);
        $parameter->initOperation($operation);
        $operation->validateParameter();

        if (($profileExpression = $this->getProfileExpression($cswConfig->getProfileMapping(), $expression))) {
            $expression->setResultExpression(
                $expression->andx(
                    array(
                        $operation->getConstraint()->getResultExpression(),
                        $profileExpression,
                    )
                )
            );
        } else {
            $expression->setResultExpression($operation->getConstraint());
        }

        $pluginLocation = $this->getProfileLocations($cswConfig->getProfileMapping());
        $templateName = self::getTemplateForElementSetName($operation->getElementSetName());
        $offset = $operation->getStartPosition() - 1;
        $this->metadataSearch
            ->setPage(0)// use no page
            ->setHits($operation->getMaxRecords())
            ->setOffset($offset)
            ->setSource($cswConfig->getSource())
            ->setExpression($expression)
            ->find();

        $time = new \DateTime();
        $matched = $this->metadataSearch->getResultCount();
        $records = $this->metadataSearch->getResult();
        $next = $offset + count($records) + 1;
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
     */
    public function transaction(TransactionParameter $parameter, CswEntity $cswConfig)
    {
        $operation = new DescribeRecord($cswConfig);
        $operationName = $parameter->getOperationName();
        if ($operationName !== 'Transaction') {
            throw new CswException('request', CswException::OperationNotSupported);
        }
        $operation = new Transaction($cswConfig);
        $parameter->initOperation($operation);
        while (($action = $parameter->nextAction($operation))) {
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
     * @param CswEntity $entity
     * @param TransactionAction $action
     * @param TransactionParameter $handler
     * @return int
     */
    public function doInsert(CswEntity $entity, TransactionAction $action, TransactionParameter $handler)
    {
        $inserted = 0;
        foreach ($action->getItems() as $mdElm) {
            $hl = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdElm);
            $hls = $entity->getProfileMapping();
            if (isset($hls[$hl])) {
                $xml = $mdElm->ownerDocument->saveXML($mdElm);
                $plugin = $this->plugin->getPlugin($hls[$hl]);
//               $file = $this->kernel->locateResource('@'.$plugin['class_name'].'/Resources/import/metadata.xml.json');
//                $parser = new XmlParser($xml, new XmlParserFunctions());
//                $array = $parser
//                    ->loadSchema(file_get_contents($file))
//                    ->parse();
////                return isset($array['p']) ? $array['p'] : array();
//                $this->metadata->saveObject(isset($array['p']) ? $array['p'] : array());
                $inserted++;
            } else {
                $this->log($entity, 'warning', 'insert', '', 'Type: $hl ist nicht unterstützt');
            }
        }

        return $inserted;
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
     * @param CswEntity $entity
     * @param TransactionAction $action
     * @param TransactionParameter $handler
     * @return int
     */
    public function doUpdate(CswEntity $entity, TransactionAction $action, TransactionParameter $handler)
    {
        $updated = 0;
        foreach ($action->getItems() as $mdElm) {
            $hl = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdElm);
            $ident = $handler->valueFor('./gmd:fileIdentifier/gco:CharacterString/text()', $mdElm);
            $hls = $entity->getProfileMapping();
            if (isset($hls[$hl])) {

            } else {
                // TODO log unsupported hierarchyLevel
//                $this->logger->warning()
            }
        }

        return $updated;
    }

    /**
     * @param CswEntity $entity
     * @param TransactionAction $action
     * @param TransactionParameter $handler
     * @return int
     */
    public function doDelete(CswEntity $entity, TransactionAction $action, TransactionParameter $handler)
    {
        $deleted = 0;
        foreach ($action->getItems() as $item) {
            // TODO do delete
        }

        return $deleted;
    }

    protected function getSchemaFile($pluginClassName)
    {
        return $this->kernel->locateResource('@'.$pluginClassName.'/Resources/import/metadata.xml.json');
    }

    private function getProfileMapping(CswEntity $cswConfig, Expression $expression)
    {
        $profileMapping = $cswConfig->getProfileMapping();
        $or = array();
        $pluginLocation = array();
        foreach ($profileMapping as $hierarchyLevel => $profile) {
            $or[] = $expression->andx(
                array(
                    $expression->eq('hierarchyLevel', $hierarchyLevel),
                    $expression->eq('profile', $profile),
                )
            );
            if (!isset($pluginLocation[$profile])) {
                $plugin = $this->plugin->getPlugin($profile);
                $pluginLocation[$profile] = array(
                    'sf' => '@'.$plugin['class_name'].':Csw:',
                    'full' => $this->kernel->locateResource('@'.$plugin['class_name'].'/Resources/'),
                );
            }
        }
    }

    /**
     * @param array $profileMapping
     * @param Expression $expression
     * @return mixed|null
     */
    private function getProfileExpression(array $profileMapping, Expression $expression)
    {
        $or = array();
        foreach ($profileMapping as $hierarchyLevel => $profile) {
            $or[] = $expression->andx(
                array(
                    $expression->eq('hierarchyLevel', $hierarchyLevel),
                    $expression->eq('profile', $profile),
                )
            );
        }
        if (count($or) > 1) {
            return $expression->orx($or);
        } elseif (count($or) === 1) {
            return $or[0];
        } else {
            return null;
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
