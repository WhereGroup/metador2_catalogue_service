<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * The class DescribeRecord is a representation of a OGC CSW DescribeRecord operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class DescribeRecord extends AOperation
{
    /**
     * {@inheritdoc}
     */
    protected static $parameterMap = array(
        'version' => '/' . Csw::CSW_PREFIX . ':DescribeRecord/@version',
        'service' => '/' . Csw::CSW_PREFIX . ':DescribeRecord/@service',
        'typeName' => '/' . Csw::CSW_PREFIX . ':DescribeRecord/' . Csw::CSW_PREFIX . ':TypeName/text()',
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
        $this->typeName     = $this->typeNameList[0];
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
    public static function getParameterMap()
    {
        return self::$parameterMap;
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
//
//    /**
//     * Sets typeName
//     * @param type $typeName
//     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\DescribeRecord
//     */
//    public function setTypeName($typeName)
//    {
//        if ($typeName && is_string($typeName)) { # GET request
//            $this->typeName = self::parseCsl($typeName);
//        } elseif ($typeName && is_array($typeName)) { # POST request
//            foreach ($typeName as $item) {
//                if (!isset($this->typeNameList[$item])) {
//                    $this->addCswException('section', CswException::InvalidParameterValue);
//                }
//            }
//            $this->typeName = $typeName;
//        }
//        return $this;
//    }

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
                if ($value && is_string($value)) { # GET request
                    $typeName = self::parseCsl($value);
                } elseif ($value && is_array($value)) { # POST request
                    $typeName = $value;
                }
                foreach ($typeName as $item) {
                    if (!in_array($item, $this->typeNameList)) {
                        $this->addCswException('typeName', CswException::InvalidParameterValue);
                    }
                }
                $this->typeName = $typeName;
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