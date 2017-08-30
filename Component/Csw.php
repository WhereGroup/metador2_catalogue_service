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
        $uuid = $expression->in('uuid', $operation->getId());
        // add supported hierarchyLevels und profiles from given csw configuration into expression
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
                    'sf' => '@'.$plugin['class_name'].':Export:',
                    'full' => $this->kernel->locateResource('@'.$plugin['class_name'].'/Resources/views/Export/'),
                );
            }
        }
        // add expression into Expression
        $expression->setResultExpression(
            $expression->andx(
                array(
                    $uuid,
                    $expression->orx($or),
                )
            )
        );
        $this->metadataSearch
            ->setPage(1)
            ->setHits(100)// set max count for GetRecordById ???
            ->setSource($cswConfig->getSource())
//            ->setProfile('metador_service_profile')
            ->setExpression($expression)
            ->find();
        switch ($operation->getElementSetName()) {
            case 'full':
                $templateName = 'metadata.xml.twig';
                break;
            case 'summary':
                $templateName = 'metadata.xml.twig';
                break;
            case 'brief':
                $templateName = 'metadata.xml.twig';
                break;
        }
        $test = $this->metadataSearch->getResult();

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

        $expression = $this->metadataSearch->createExpression();
//
//        $name = 'm';
//        /** @var QueryBuilder $qb */
//        $qb = $this->csw->getMetadata()->getQueryBuilder($name);
//        $filter = new FilterCapabilities();
//        $parameters = array();
//        $constraintsMap = array();
//        foreach ($this->constraintList as $key => $value) {
//            $constraintsMap = array_merge_recursive($constraintsMap, $value);
//        }
//        $constraintsMap = array_merge_recursive(
//            isset($this->geometryQueryables) ? $this->geometryQueryables : array(),
//            $constraintsMap
//        );
//
//        $num = count($parameters);
//        $finalExpr = new Expr\Comparison($name.'.public', '=', ':public'.$num);
//        $parameters['public'.$num] = true;
//        $filterExpr = null;
//        if ($this->constraint) {
//            $filterExpr = $filter->generateFilter($qb, $name, $constraintsMap, $parameters, $this->constraint);
//        }
//        $qb->select('count('.$name.'.id)');
//        if ($filterExpr) {
//            $finalExpr = new Expr\Andx(array($filterExpr, $finalExpr));
//        }
//        $qb->add('where', $finalExpr)->setParameters($parameters);
//        $query = $qb->getQuery();
//        $matched = $qb->getQuery()->getSingleScalarResult();
//        $returned = $matched;
//        $results = array();
//        if ($this->resultType === self::RESULTTYPE_RESULTS) {# || $this->resultType === self::RESULTTYPE_VALIDATE) {
//            $qb->select($name);
//            $qb->add('where', $finalExpr)->setParameters($parameters);
//            $qb->setFirstResult($this->startPosition - 1)
//                ->setMaxResults($this->maxRecords);
//            FilterCapabilities::generateSortBy($qb, $name, $constraintsMap, $this->sortBy);
//
//            $results = $qb->getQuery()->getResult();
//            $returned = count($results);
//        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
//<csw:GetRecordsResponse xmlns:ows=\"http://www.opengis.net/ows\"  xmlns:csw=\"http://www.opengis.net/cat/csw/2.0.2\"
// xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
// xsi:schemaLocation=\"http://www.opengis.net/cat/csw/2.0.2 http://schemas.opengis.net/csw/2.0.2/CSW-discovery.xsd\">";
//
//        $time = new \DateTime();
//        $timestamp = $time->format('Y-m-d\TH:i:s');
//
//        if (isset($this->requestId)) {
//            $xml .= "\n<csw:RequestId>".$timestamp."</csw:RequestId>";
//        }
//
//        $xml .= "\n<csw:SearchStatus timestamp=\"".$timestamp."\" />
//<csw:SearchResults numberOfRecordsMatched=\"".$matched."\" numberOfRecordsReturned=\"".$returned
//            ."\" elementSet=\"".$this->elementSetName."\" nextRecord=\"".($this->startPosition - 1)."\">";
//
//        foreach ($results as $record) {
//            $className = $this->csw->container->get('metador_plugin')->getPluginClassName($record->getProfile());
//            $xml .= "\n".$this->csw->getTemplating()->render(
//                    $className.":Export:metadata.xml.twig",
//                    array('p' => $record->getObject())
//                );
//        }
//
//        $xml .= "\n</csw:SearchResults>
//</csw:GetRecordsResponse>";

        return $xml;
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
}
