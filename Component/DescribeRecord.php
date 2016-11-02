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
        'version'      => '/' . Csw::CSW_PREFIX . ':DescribeRecord/@version',
        'service'      => '/' . Csw::CSW_PREFIX . ':DescribeRecord/@service',
        'typeName'     => '/' . Csw::CSW_PREFIX . ':DescribeRecord/' . Csw::CSW_PREFIX . ':TypeName/text()',
        'outputFormat' => '/' . Csw::CSW_PREFIX . ':DescribeRecord/@outputFormat',
    );

    /**
     * {@inheritdoc}
     */
    protected $name = 'DescribeRecord';

    /**
     * The list of all supported "typeNames"
     * @var array $typeNameList
     */
    protected $typeNameList;

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
    public function __construct(Csw $csw, $configuration)
    {
        parent::__construct($csw, $configuration);
        $this->typeNameList = $configuration['typeNameList'];
        $this->typeName     = $this->typeNameList;
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
     * Returns typeNameList
     * @return array
     */
    public function getTypeNameList()
    {
        return $this->typeNameList;
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
     * Sets typeNameList
     * @param array $typeNameList
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\DescribeRecord
     */
    public function setTypeNameList($typeNameList)
    {
        $this->typeNameList = $typeNameList;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array(
            'typeName' => $this->typeNameList,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'typeName':
                $typeName = array();
                if ($value && is_string($value)) { # GET request or POST single typeName
                    $typeName = self::parseCsl($value);
                } elseif ($value && is_array($value)) { # POST request
                    $typeName = $value;
                }
                if ($this->isListAtList($name, $typeName, $this->typeNameList, false)) {
                    $this->typeName = $typeName;
                }
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
        return $this->csw->getTemplating()->render(
                $this->templates[$this->getOutputFormat()],
                array(
                'describerecord' => $this
                )
        );
    }
}
