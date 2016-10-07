<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use WhereGroup\CoreBundle\Component\Metadata;
use WhereGroup\PluginBundle\Component\Plugin;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 */
class Csw
{
    const SERVICE     = 'CSW';
    const VERSIONLIST = array('2.0.2');
    const VERSION     = '2.0.2';

    protected $requestStack = null;
    protected $metadata     = null;
    protected $plugin       = null;
    protected $httpGet;
    protected $httpPost;
    protected $sections     = array(
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
        'ServiceIdentification' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\ServiceIdentification',
            'title' => 'Catalogue-Service WhereGroup',
            'abstract' => 'Catalogue-Service WhereGroup',
            'keywords' => array('CS-W', 'ISO19119', 'ISO19115', 'WhereGroup', 'Catalog Service', 'metadata'),
            'versions' => array('2.0.2'),
            'fees' => 'none',
            'accessConstraints' => array('none')
        ),
        'OperationsMetadata' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\OperationsMetadata'),
        'FilterCapabilities' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\FilterCapabilities'),
    );

    /**
     * The Operations GetCapbilities, GetRecords, DescribeRecord are required.
     * @var type
     */
    protected $operations   = array(
        'GetCapabilities' => array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\GetCapabilities',
            'outpurFormats' => array('application/xml' => "CatalogueServiceBundle:CSW:capabilities_response.xml.twig"),
            'postEncodings' => array('XML'),
            'http' => array('get' => true, 'post' => true)
        ),
        'GetRecordById' =>  array(
            'class' => 'Plugins\WhereGroup\CatalogueServiceBundle\Component\GetRecordById',
            'outpurFormats' => array('application/xml' => null),
            'http' => array('get' => true, 'post' => false))
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
    public function __construct(
    RequestStack $requestStack, Metadata $metadata, Plugin $plugin, $templating
    )
    {
        $this->requestStack = $requestStack;
        $this->metadata     = $metadata;
        $this->plugin       = $plugin;
        $this->templating   = $templating;
        $req = $this->requestStack->getCurrentRequest();
        $url = $req->getSchemeAndHttpHost() . $req->getBaseUrl() . $req->getPathInfo();
        $this->httpGet      = ($this->httpPost     = $url) . '?';
    }

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

    public function getParameterHandler()
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request->getMethod() === 'GET') { # GET
            return new GetParameterHandler($request->query->all());
        } else { # POST
            return new PostParameterHandler($request->getContent());
        }
    }

    /**
     *
     * @param \Plugins\WhereGroup\CatalogueServiceBundle\Component\AParameterHandler $handler
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
     */
    public function getOperation(AParameterHandler $handler)
    {
        try {
            $operationStr = $handler->getOperation();
            $configuration = $this->operations[$operationStr];
            $fullClass    = $configuration['class'];
            /**
             * @var AOperation $operation
             */
            return new $fullClass($this, $configuration);
        } catch (\Exception $e) {
            throw new CswException('request', CswException::OperationNotSupported);
        }
    }

    public function createContent(AParameterHandler $handler, AOperation $operation)
    {
        return $operation->createResult($handler);
    }

    /**
     * @param $id
     * @return string
     */
    public function getRecordById($id)
    {
        /** @var \WhereGroup\CoreBundle\Entity\Metadata $entity */
        $entity = $this->metadata->getByUUID($id);

        // get data object
        $p = $entity->getObject();

        // get profile
        $className = $this->plugin->getPluginClassName($p['_profile']);

        // render metadata
        return $this->templating->render($className . ":Export:metadata.xml.twig",
                array(
                "p" => $p
        ));
    }
}