<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\DependencyInjection\ContainerAwareTrait;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\HttpFoundation\RequestStack;
//use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
//use WhereGroup\CoreBundle\Component\Metadata;
//use WhereGroup\PluginBundle\Component\Plugin;
//use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostSaxParameterHandler;
//use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameterHandler;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 */
class Csw
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|null|\Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository
     */
    protected $repo = null;

    const ENTITY = "CatalogueServiceBundle:Csw";

    /** @param EntityManagerInterface $em */
    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(self::ENTITY);
    }

    public function __destruct()
    {
        unset(
            $this->repo
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
     * @return mixed
     */
    public function findBySlug($slug)
    {
        return $this->repo->findOneBySlug($slug);
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

//    /**
//     * The element prefix for csw namespace
//     */
//    const CSW_PREFIX = 'csw';
//
//    /**
//     * The uri for csw namespace
//     */
//    const CSW_NAMESPACE = 'http://www.opengis.net/cat/csw/2.0.2';
//
//    /**
//     * The service name
//     */
//    const SERVICE = 'CSW';
//
//    /**
//     * The version
//     */
//    const VERSION = '2.0.2';
//
//    /**
//     * The supported versions
//     * @var $array $VERSIONLIST
//     */
//    static $VERSIONLIST     = array('2.0.2');
//    protected $requestStack = null;
//    protected $metadata     = null;
//    protected $plugin       = null;
//
//    /**
//     * URL for GET requests
//     * @var string $httpGet
//     */
//    protected $httpGet;
//
//    /**
//     * URL for POST requests
//     * @var string $httpPost
//     */
//    protected $httpPost;
//
//    /**
//     * The configuration parameters of supported sections
//     * @var array $sections
//     */
//    protected $sections = array();
//
//    /**
//     * The configuration parameters of supported operations
//     * @var array $sections
//     */
//    protected $operations = array();
//
//    /** @var TimedTwigEngine $templating */
//    protected $templating = null;
//
//    /**
//     * Csw constructor.
//     * @param RequestStack $requestStack
//     * @param Metadata $metadata
//     * @param Plugin $plugin
//     * @param $templating
//     */
//    public function __construct(ContainerInterface $container, RequestStack $requestStack, Metadata $metadata, Plugin $plugin, $templating)
//    {
//
//        $this->container    = $container;
//        $this->requestStack = $requestStack;
//        $this->metadata     = $metadata;
//        $this->plugin       = $plugin;
//        $this->templating   = $templating;
//        $req                = $this->requestStack->getCurrentRequest();
//        $url                = $req->getSchemeAndHttpHost() . $req->getBaseUrl() . $req->getPathInfo();
//        $this->httpGet      = ($this->httpPost = $url) . '?';
//        $this->operations   = $container->getParameter('csw')['Operations'];
//        $this->sections     = $container->getParameter('csw')['Sections'];
//    }
//
//    /**
//     * Csw destructor
//     */
//    public function __destruct()
//    {
//        unset(
//            $this->requestStack, $this->metadata, $this->plugin, $this->templating, $this->operations, $this->sections
//        );
//    }
//
//    public function getMetadata()
//    {
//        return $this->metadata;
//    }
//
//    public function getOperations()
//    {
//        return $this->operations;
//    }
//
//    public function getTemplating()
//    {
//        return $this->templating;
//    }
//
//    public function getSections()
//    {
//        return $this->sections;
//    }
//
//    public function getHttpGet()
//    {
//        return $this->httpGet;
//    }
//
//    public function getHttpPost()
//    {
//        return $this->httpPost;
//    }
//
//    public function getRequestStack()
//    {
//        return $this->requestStack;
//    }
//
//    /**
//     * Creates an operation for a given operation name
//     * @param type $operationName
//     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
//     * @throws CswException if the operation is not supported
//     */
//    public function operationForName($operationName)
//    {
//        try {
//            $configuration = $this->operations[$operationName];
//            $fullClass     = $configuration['class'];
//
//            return new $fullClass($this, $configuration);
//        }
//        catch (\Exception $e) {
//            throw new CswException('request', CswException::OperationNotSupported);
//        }
//    }
//
//    /**
//     * Creates an operation
//     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
//     */
//    public function createOperation()
//    {
//        $handler = null;
//        $request = $this->requestStack->getCurrentRequest();
//
//        if ($request->getMethod() === 'GET') {
//            $handler = GetParameterHandler::create($this);
//        }
//
//        if ($request->getMethod() === 'POST') {
//            $handler = PostSaxParameterHandler::create($this); #$request->getContent());
//        }
//
//        return $handler->getOperation();
//    }
////
////    /**
////     * @param $id
////     * @return string
////     */
////    public function getRecordById($id)
////    {
////        /** @var \WhereGroup\CoreBundle\Entity\Metadata $entity */
////        $entity = $this->metadata->getByUUID($id);
////
////        // get data object
////        $p = $entity->getObject();
////
////        // get profile
////        $className = $this->plugin->getPluginClassName($p['_profile']);
////
////        // render metadata
////        return $this->templating->render($className . ":Export:metadata.xml.twig",
////                array(
////                "p" => $p
////        ));
////    }
}
