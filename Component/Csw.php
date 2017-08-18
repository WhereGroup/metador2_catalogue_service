<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\IParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\TransactionParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use WhereGroup\CoreBundle\Component\Metadata;
use WhereGroup\PluginBundle\Component\Plugin;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 * @author Paul Schmidt <panadium@gmx.de>
 */
class Csw
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|null|\Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository
     */
    private $repo = null;


    const ENTITY = "CatalogueServiceBundle:Csw";

    /**
     * @var null|RequestStack
     */
    private $requestStack = null;

    /**
     * @var null|RouterInterface
     */
    private $router = null;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * Csw constructor.
     * @param EntityManagerInterface $em
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        RouterInterface $router,
        Plugin $plugin,
        Metadata $metadata
    ) {
        $this->kernel = $kernel;
        $this->repo = $em->getRepository(self::ENTITY);
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->plugin = $plugin;
        $this->metadata = $metadata;
    }

    /**
     * Csw destructor.
     */
    public function __destruct()
    {
        unset(
            $this->repo,
            $this->requestStack,
            $this->router
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
     * @param CswEntity $entity
     * @param IParameterHandler $handler
     * @param $templating
     * @return mixed|DescribeRecord|GetRecordById|GetRecords
     * @throws CswException
     */
    public function doBasic(CswEntity $entity, IParameterHandler $handler, $templating)
    {
        $operationName = $handler->getOperationName();
        switch ($operationName) {
            case 'GetCapabilities':
                $params = array(
                    'source' => $entity->getSource(),
                    'slug' => $entity->getSlug(),
                );
                $getCapabilities = new GetCapabilities(
                    $entity,
                    $this->router->generate('csw_default', $params, UrlGeneratorInterface::ABSOLUTE_URL),
                    $this->router->generate('csw_manager', $params, UrlGeneratorInterface::ABSOLUTE_URL)
                );

                return $this->doGetCapabilities($handler, $getCapabilities, $templating);
            case 'DescribeRecord':
                return $this->doDescribeRecord($handler, new DescribeRecord($entity), $templating);
            case 'GetRecordById':
                return new GetRecordById($entity);
            case 'GetRecords':
                return new GetRecords($entity);
            default:
                throw new CswException('request', CswException::OperationNotSupported);
        }
    }

    /**
     * @param IParameterHandler $handler
     * @param GetCapabilities $operation
     * @param $templating
     * @return mixed
     */
    public function doGetCapabilities(IParameterHandler $handler, GetCapabilities $operation, $templating)
    {
        $handler->initOperation($operation);
        $operation->validateParameter();

        return $templating->render(
            'CatalogueServiceBundle:CSW:getcapabilities_response.xml.twig',
            array(
                'getcap' => $operation,
            )
        );
    }

    /**
     * @param IParameterHandler $handler
     * @param DescribeRecord $operation
     * @param $templating
     * @return mixed
     */
    public function doDescribeRecord(IParameterHandler $handler, DescribeRecord $operation, $templating)
    {
        $handler->initOperation($operation);
        $operation->validateParameter();

        return $templating->render(
            'CatalogueServiceBundle:CSW:describerecord.xml.twig',
            array(
                'descrec' => $operation,
            )
        );
    }

    /**
     * @param IParameterHandler $handler
     * @param GetRecordById $operation
     * @param $templating
     * @return mixed
     */
    public function doGetRecordById(IParameterHandler $handler, GetRecordById $operation, $templating)
    {
        $handler->initOperation($operation);
        $operation->validateParameter();

        $xml = '';

        try {
            foreach ($this->id as $id) {
                $record = $this->csw->getMetadata()->getByUUID($id);

                if (!$record->getPublic()) {
                    // TODO: maby exception
                    continue;
                }

                // GET Template
                $className = $this->csw->container->get('metador_plugin')->getPluginClassName($record->getProfile());
                $xml .= "\n".$this->csw->getTemplating()->render(
                        $className.":Export:metadata.xml.twig",
                        array('p' => $record->getObject())
                    );
            }
        } catch (\Exception $e) {
            throw new CswException('id', CswException::NoApplicableCode);
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <csw:GetRecordByIdResponse xmlns:csw=\"http://www.opengis.net/cat/csw/2.0.2\">
                $xml
            </csw:GetRecordByIdResponse>";
    }

    /**
     * @param IParameterHandler $handler
     * @param GetRecords $operation
     * @param $templating
     * @return mixed
     */
    public function doGetRecords(IParameterHandler $handler, GetRecords $operation, $templating)
    {
        $handler->initOperation($operation);
        $operation->validateParameter();

        $name = 'm';
        /** @var QueryBuilder $qb */
        $qb = $this->csw->getMetadata()->getQueryBuilder($name);
        $filter = new FilterCapabilities();
        $parameters = array();
        $constarintsMap = array();
        foreach ($this->constraintList as $key => $value) {
            $constarintsMap = array_merge_recursive($constarintsMap, $value);
        }
        $constarintsMap = array_merge_recursive(
            isset($this->geometryQueryables) ? $this->geometryQueryables : array(), $constarintsMap);

        $num = count($parameters);
        $finalExpr = new Expr\Comparison($name.'.public', '=', ':public'.$num);
        $parameters['public'.$num] = true;
        $filterExpr = null;
        if ($this->constraint) {
            $filterExpr = $filter->generateFilter($qb, $name, $constarintsMap, $parameters, $this->constraint);
        }
        $qb->select('count('.$name.'.id)');
        if ($filterExpr) {
            $finalExpr = new Expr\Andx(array($filterExpr, $finalExpr));
        }
        $qb->add('where', $finalExpr)->setParameters($parameters);
        $query = $qb->getQuery();
        $matched = $qb->getQuery()->getSingleScalarResult();
        $returned = $matched;
        $results = array();
        if ($this->resultType === self::RESULTTYPE_RESULTS) {# || $this->resultType === self::RESULTTYPE_VALIDATE) {
            $qb->select($name);
            $qb->add('where', $finalExpr)->setParameters($parameters);
            $qb->setFirstResult($this->startPosition - 1)
                ->setMaxResults($this->maxRecords);
            FilterCapabilities::generateSortBy($qb, $name, $constarintsMap, $this->sortBy);

            $results = $qb->getQuery()->getResult();
            $returned = count($results);
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<csw:GetRecordsResponse xmlns:ows=\"http://www.opengis.net/ows\"  xmlns:csw=\"http://www.opengis.net/cat/csw/2.0.2\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.opengis.net/cat/csw/2.0.2 http://schemas.opengis.net/csw/2.0.2/CSW-discovery.xsd\">";

        $time = new \DateTime();
        $timestamp = $time->format('Y-m-d\TH:i:s');

        if (isset($this->requestId)) {
            $xml .= "\n<csw:RequestId>".$timestamp."</csw:RequestId>";
        }

        $xml .= "\n<csw:SearchStatus timestamp=\"".$timestamp."\" />
<csw:SearchResults numberOfRecordsMatched=\"".$matched."\" numberOfRecordsReturned=\"".$returned."\" elementSet=\"".$this->elementSetName."\" nextRecord=\"".($this->startPosition - 1)."\">";

        foreach ($results as $record) {
            $className = $this->csw->container->get('metador_plugin')->getPluginClassName($record->getProfile());
            $xml .= "\n".$this->csw->getTemplating()->render(
                    $className.":Export:metadata.xml.twig",
                    array('p' => $record->getObject())
                );
        }

        $xml .= "\n</csw:SearchResults>
</csw:GetRecordsResponse>";

        return $xml;
    }

    public function doTransaction(CswEntity $entity, TransactionParameterHandler $handler, $templating)
    {
//        $handler = new TransactionParameterHandler($xmlString);
        $operationName = $handler->getOperationName();
        if ($operationName !== 'Transaction') {
            throw new CswException('request', CswException::OperationNotSupported);
        }
        $operation = new Transaction($entity);
        $handler->initOperation($operation);
        while (($action = $handler->nextAction($operation))) {
            switch ($action->getType()) {
                case Transaction::INSERT:
                    $this->doInsert($entity, $action, $handler);
                    break;
                case Transaction::UPDATE:
                    $this->doUpdate($action);
                    break;
                case Transaction::DELETE:
                    $this->doDelete($action);
                    break;
            }
        }
    }

    public function doInsert(CswEntity $entity, TransactionAction $action, TransactionParameterHandler $handler)
    {
        foreach ($action->getItems() as $mdElm) {
            $hl = $handler->valueFor('./gmd:hierarchyLevel[1]/gmd:MD_ScopeCode/text()', $mdElm);
            $hls = $entity->getProfileMapping();
            if (isset($hls[$hl])) {
                $xml = $mdElm->ownerDocument->saveXML($mdElm);
                $plugin = $this->plugin->getPlugin($hls[$hl]);
                $file = $this->kernel->locateResource('@'.$plugin['class_name'].'/Resources/import/metadata.xml.json');
                $parser = new XmlParser($xml, new XmlParserFunctions());
                $array = $parser
                    ->loadSchema(file_get_contents($file))
                    ->parse();
//                return isset($array['p']) ? $array['p'] : array();
                $this->metadata->saveObject(isset($array['p']) ? $array['p'] : array());
            } else {
                // TODO log unsupported hierarchyLevel
            }
//            $entity->get
//
//
        }
    }

    public function doUpdate(TransactionAction $action)
    {
        foreach ($action->getItems() as $item) {
            // TODO do update
        }
    }

    public function doDelete(TransactionAction $action)
    {
        foreach ($action->getItems() as $item) {
            // TODO do delete
        }
    }
//
//    private function parseElement()
//    {
//        $parser = new XmlParser($xml, new XmlParserFunctions());
//
//        $array = $parser
//            ->loadSchema(file_get_contents($this->getSchemaFile($pluginClassName)))
//            ->parse();
//
//
//        return isset($array['p']) ? $array['p'] : array();
//    }
    protected function getSchemaFile($pluginClassName)
    {
        return $this->kernel->locateResource('@'.$pluginClassName.'/Resources/import/metadata.xml.json');
    }
}
