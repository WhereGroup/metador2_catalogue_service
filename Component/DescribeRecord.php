<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;


/**
 * The class DescribeRecord is a representation of the OGC CSW DescribeRecord operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class DescribeRecord extends AOperation
{
    /**
     * {@inheritdoc}
     */
    protected static $parameterMap = array(
        'version' => '/csw:DescribeRecord/@version',
        'service' => '/csw:DescribeRecord/@service',
        'typeName' => '/csw:DescribeRecord/csw:TypeName/text()',
        'outputFormat' => '/csw:DescribeRecord/@outputFormat',
    );

    /**
     * {@inheritdoc}
     */
    public function getGETParameterMap()
    {
        return array_keys(self::$parameterMap);
    }

    /**
     * {@inheritdoc}
     */
    public function getPOSTParameterMap()
    {
        $parameters = array();
        foreach (self::$parameterMap as $key => $value) {
            if ($value !== null) {
                $parameters[$value] = $key;
            }
        }

        return $parameters;
    }

    /* Request parameters */

    /**
     * The request parameter namespace
     * @var string $namespace
     */
    protected $namespace; // not yet implemented

    /**
     * The request parameter typeName
     * @var string $typeName
     */
    protected $typeName;

    /**
     * {@inheritdoc}
     */
    public function __construct(\Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity)
    {
        parent::__construct($entity);
        $this->typeName = array('gmd:MD_Metadata');
    }

    /**
     * Returns typeName
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * Returns typeName
     * @return string
     */
    public function setTypeName($typeName)
    {
        $_typeName = array();
        if ($typeName && is_string($typeName)) { # GET request or POST single typeName
            $_typeName = self::parseCsl($typeName);
        } elseif ($typeName && is_array($typeName)) { # POST request
            $_typeName = $typeName;
        }
        if ($this->isListAtList('typeName', $_typeName, $this->typeName, false)) {
            $this->typeName = $_typeName;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'typeName':
                $this->setTypeName($value);
                break;
            default:
                parent::setParameter($name, $value);
        }
    }
}
