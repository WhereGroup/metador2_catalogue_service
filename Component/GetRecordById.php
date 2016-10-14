<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of Operation
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetRecordById extends AFindRecord
{
    /**
     * {@inheritdoc}
     */
    static $parameterMap = array(
        'version' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@version',
        'service' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@service',
        'outputSchema' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@outputSchema',
        'outputFormat' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@outputFormat',
        'ElementSetName' => '/' . Csw::CSW_PREFIX . ':GetRecordById/ElementSetName/text()',
        'id' => '/' . Csw::CSW_PREFIX . ':GetRecordById/' . Csw::CSW_PREFIX . ':Id/text()',
    );
    protected $elementSetNameList;
    protected $elementSetName;
    protected $outputSchema;
    protected $id;

    /**
     * {@inheritdoc}
     */
    protected $name = 'GetRecordById';

    /**
     * {@inheritdoc}
     */
    public function __construct(Csw $csw = null, $configuration = array())
    {
        parent::__construct($csw, $configuration);
        $this->elementSetNameList = $configuration['elementSetNameList'];
        $this->elementSetName     = 'summary';
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        unset(
            $this->elementSetNameList, $this->elementSetName, $this->$outputSchema, $this->id
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

    public function getElementSetNameList()
    {
        return $this->elementSetNameList;
    }

    public function getElementSetName()
    {
        return $this->elementSetName;
    }

    public function setElementSetNameList($elementSetNameList)
    {
        $this->elementSetNameList = $elementSetNameList;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array_merge(parent::getParameters(),
            array(
            'ElementSetName' => $this->elementSetNameList
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        return array(
            'PostEncoding' => $this->postEncodingList
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'ElementSetName':
                if ($value && !in_array($value, $this->elementSetNameList)) {
                    throw new CswException('ElementSetName', CswException::INVALIDPARAMETERVALUE);
                } elseif ($value && in_array($value, $this->elementSetNameList)) {
                    $this->elementSetName = $value;
                }
                break;
            case 'id':
                if ($value && is_string($value)) {
                    $this->id = $this->parseCsl($value);
                } elseif ($value && is_array($value)) {
                    $this->id = $value;
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
        $results = array();
        foreach ($this->id as $id) {
            $results[] = $this->csw->getMetadata()->getByUUID($id)->getObject();
        }
        return $this->csw->getTemplating()->render(
                $this->templates[$this->getOutputFormat()],
                array(
                'records' => $results
                )
        );
    }
}