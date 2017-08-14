<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;

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
        'version' => '/'.self::PREFIX.':DescribeRecord/@version',
        'service' => '/'.self::PREFIX.':DescribeRecord/@service',
        'typeName' => '/'.self::PREFIX.':DescribeRecord/'.self::PREFIX.':TypeName/text()',
        'outputFormat' => '/'.self::PREFIX.':DescribeRecord/@outputFormat',
    );

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
    public function __construct(CswEntity $entity)
    {
        parent::__construct($entity);
        $this->typeName = array('gmd:MD_Metadata');
        $this->template = 'CatalogueServiceBundle:CSW:describerecord.xml.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        unset(
            $this->typeNameList, $this->typeName
        );
        parent::__destruct();
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

    /**
     * {@inheritdoc}
     */
    protected function render($templating)
    {
        return $templating->render(
            $this->template,
            array(
                'descrec' => $this,
            )
        );
    }
}
