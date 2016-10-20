<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use WhereGroup\CoreBundle\Component\Metadata;
use WhereGroup\PluginBundle\Component\Plugin;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostSaxParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameterHandler;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 */
class Csw
{
    /**
     * The element prefix for csw namespace
     */
    const CSW_PREFIX = 'csw';
    /**
     * The uri for csw namespace
     */
    const CSW_NAMESPACE = 'http://www.opengis.net/cat/csw/2.0.2';
    /**
     * The service name
     */
    const SERVICE     = 'CSW';
    /**
     * The version
     */
    const VERSION     = '2.0.2';
    /**
     * The supported versions
     * @var $array $VERSIONLIST
     */
    static $VERSIONLIST = array('2.0.2');

    protected $requestStack = null;
    protected $metadata     = null;
    protected $plugin       = null;
    
    /**
     * URL for GET requests
     * @var string $httpGet
     */
    protected $httpGet;

    /**
     * URL for POST requests
     * @var string $httpPost
     */
    protected $httpPost;

    /**
     * The configuration parameters of supported sections
     * @var array $sections
     */
    protected $sections     = array(
        'ServiceIdentification' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\ServiceIdentification',
            'title' => 'Catalogue-Service WhereGroup',
            'abstract' => 'Catalogue-Service WhereGroup',
            'keywords' => array('CS-W', 'ISO19119', 'ISO19115', 'WhereGroup', 'Catalog Service', 'metadata'),
            'versions' => array('2.0.2'),
            'fees' => 'none',
            'accessConstraints' => array('none')
        ),
        'ServiceProvider' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\ServiceProvider',
            'providerName' => 'WhereGroup',
            'providerSite' => 'http://www.wheregroup.com',
            'serviceContact' => array(
                'individualName' => 'NAME',
                'positionName' => 'support',
                'contactInfo' => array(
                    'phone' => array(
                        'voice' => '+49-????????????',
                        'facsimile' => '+49-??????????????'
                    ),
                    'address' => array(
                        'deliveryPoint' => 'Teststr. 12',
                        'city' => 'Bonn',
                        'administrativeArea' => 'NRW',
                        'postalCode' => '55555',
                        'country' => 'Germany',
                        'electronicMailAddress' => 'info@xxxxx.com'
                    ),
                    'onlineResource' => 'http://www.xxxxxxx.com'
                )
            )
        ),
        'OperationsMetadata' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\OperationsMetadata'),
        'Filter_Capabilities' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter\FilterCapabilities'),
    );


    /**
     * The configuration parameters of supported operations
     * @var array $sections
     */
    protected $operations   = array(
        'GetCapabilities' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\GetCapabilities',
            'outpurFormatList' => array('application/xml' => "CatalogueServiceBundle:CSW:capabilities_response.xml.twig"),
            'http' => array('get' => true, 'post' => true)
        ),
        'DescribeRecord' =>  array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\DescribeRecord',
            'typeNameList' => array('gmd:MD_Metadata'),
            'outpurFormatList' => array('application/xml' => "CatalogueServiceBundle:CSW:describe_response.xml.twig"),
            #'schemaLanguage' => array(), # The default value is XMLSCHEMA, other schemas are not supported
            'http' => array('get' => true, 'post' => false)),
        'GetRecordById' =>  array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\GetRecordById',
            'outpurFormatList' => array('application/xml' => "CatalogueServiceBundle:CSW:recordbyid_collection.xml.twig"),
            'outputSchemaList' => array('http://www.isotc211.org/2005/gmd'),
            'resultTypeList' => array('results'),#('hits', 'results', 'validate'),
            'elementSetNameList' => array('full'),#('brief', 'summary', 'full'), // default value "summary" !!!
            'http' => array('get' => true, 'post' => true)),
        'GetRecords' =>  array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\GetRecords',
            'outpurFormatList' => array('application/xml' => "CatalogueServiceBundle:CSW:records_collection.xml.twig"),
            'outputSchemaList' => array('http://www.isotc211.org/2005/gmd'),
            'resultTypeList' => array('results'),#('hits', 'results', 'validate'),
            'elementSetNameList' => array('full'),#('brief', 'summary', 'full'), // default value "summary" !!!
            'constraintLanguageList' => array('FILTER'),#('FILTER', 'CQL_TEXT'),
            'typeNameList' => array('gmd:MD_Metadata'),
            'constraintList' => array(
                'SupportedISOQueryables'=> array(
                    'Identifier' => 'uuid',
                    'Title' => 'title',
                    'Abstract' => 'abstract'
                )
            ),
//            'requestId'
//            'NAMESPACE' =>
            'http' => array('get' => false, 'post' => true))
    );

    /** @var TimedTwigEngine $templating */
    protected $templating = null;

    /**
     * Csw constructor.
     * @param RequestStack $requestStack
     * @param Metadata $metadata
     * @param Plugin $plugin
     * @param $templating
     */
    public function __construct(RequestStack $requestStack, Metadata $metadata, Plugin $plugin, $templating)
    {
        $this->requestStack = $requestStack;
        $this->metadata     = $metadata;
        $this->plugin       = $plugin;
        $this->templating   = $templating;
        $req = $this->requestStack->getCurrentRequest();
        $url = $req->getSchemeAndHttpHost() . $req->getBaseUrl() . $req->getPathInfo();
        $this->httpGet      = ($this->httpPost     = $url) . '?';
    }

    /**
     * Csw destructor
     */
    public function __destruct()
    {
        unset(
            $this->requestStack,
            $this->metadata,
            $this->plugin,
            $this->templating,
            $this->operations,
            $this->sections
        );
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getOperations()
    {
        return $this->operations;
    }

    public function getTemplating()
    {
        return $this->templating;
    }


    public function getSections()
    {
        return $this->sections;
    }

    public function getHttpGet()
    {
        return $this->httpGet;
    }

    public function getHttpPost()
    {
        return $this->httpPost;
    }

    public function getRequestStack()
    {
        return $this->requestStack;
    }
    
    /**
     * Creates an operation for a given operation name
     * @param type $operationName
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
     * @throws CswException if the operation is not supported
     */
    public function operationForName($operationName)
    {
        try {
            $configuration = $this->operations[$operationName];
            $fullClass    = $configuration['class'];
            return new $fullClass($this, $configuration);
        } catch (\Exception $e) {
            throw new CswException('request', CswException::OperationNotSupported);
        }
    }

    /**
     * Creates an operation
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
     */
    public function createOperation()
    {
        $request = $this->requestStack->getCurrentRequest();
        $handler = null;
        if ($request->getMethod() === 'GET') { # GET
            $handler = new GetParameterHandler($this);
        } if ($request->getMethod() === 'POST') {  # POST
            $handler = new PostSaxParameterHandler($this);#$request->getContent());
        }
        return $handler->getOperation();
    }
//
//    /**
//     * @param $id
//     * @return string
//     */
//    public function getRecordById($id)
//    {
//        /** @var \WhereGroup\CoreBundle\Entity\Metadata $entity */
//        $entity = $this->metadata->getByUUID($id);
//
//        // get data object
//        $p = $entity->getObject();
//
//        // get profile
//        $className = $this->plugin->getPluginClassName($p['_profile']);
//
//        // render metadata
//        return $this->templating->render($className . ":Export:metadata.xml.twig",
//                array(
//                "p" => $p
//        ));
//    }
}