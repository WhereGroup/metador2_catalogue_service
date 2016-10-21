<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of Harvest
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class Harvest extends AOperation
{
    /**
     * {@inheritdoc}
     */
    static $parameterMap = array(
        'version' => '/csw:Harvest/@version',
        'service' => '/csw:Harvest/@service',
        'namespace' => '/csw:Harvest/@outputSchema',
        'source' => '/csw:Harvest/csw:Source/text()',
        'resourceType' => '/csw:Harvest/ResourceType/text()',
        'resourceFormat' => '/csw:Harvest/csw:ResourceFormat/text()',
        'responseHandler' => '/csw:Harvest/csw:ResponseHandler/text()',
        'harvestInterval' => '/csw:Harvest/csw:HarvestInterval/text()',
    );

    /**
     * {@inheritdoc}
     */
    protected $name = 'Harvest';

    protected $source;

    protected $resourceType;

    protected $resourceFormat;

    protected $responseHandler;

    protected $harvestInterval;

    /**
     * {@inheritdoc}
     */
    public function __construct(Csw $csw = null, $configuration = array())
    {
        parent::__construct($csw, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
//        unset(
//            $this->elementSetNameList, $this->elementSetName, $this->$outputSchema, $this->id
//        );
        parent::__destruct();
    }

    /**
     * {@inheritdoc}
     */
    public static function getGETParameterMap()
    {
        return array_keys(self::$parameterMap);
    }

    /**
     * {@inheritdoc}
     */
    public static function getPOSTParameterMap()
    {
        $parameters       = array();
        foreach (self::$parameterMap as $key => $value) {
            if ($value !== null) {
                $parameters[$value] = $key;
            }
        }
        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'source':
                $this->source = $value; # @TODO validate an url
                break;
            case 'resourceType':
                $this->resourceType = $value; # @TODO validate an url
                break;
            case 'resourceFormat':
                $this->resourceFormat = $value; # @TODO check format
                break;
            case 'responseHandler':
                $this->responseHandler = $value; # @TODO validate an url
                break;
            case 'harvestInterval':
                $this->harvestInterval = $value; # @TODO validate an url
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function render()
    {
        $results = array();
        foreach ($this->id as $id) {
            $results[] = $this->csw->getMetadata()->getByUUID($id);
        }
        return $this->csw->getTemplating()->render(
                $this->templates[$this->getOutputFormat()],
                array(
                'records' => $results
                )
        );
    }
}