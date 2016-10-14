<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of Operation
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class AFindRecord extends AOperation
{

    protected $outputSchemaList;
    protected $outputSchema; // default value is a first position at the list $outputSchemaList
    protected $resultTypeList;
    protected $resultType;

    /**
     * {@inheritdoc}
     */
    public function __construct(Csw $csw = null, $configuration = array())
    {
        parent::__construct($csw, $configuration);
        $this->outputSchemaList = $configuration['outputSchemaList'];
        $this->outputSchema   = $this->outputSchemaList[0];
        $this->resultTypeList = $configuration['resultTypeList'];
        $this->resultType   = $this->resultTypeList[0];
    }

    /**
     * Returns the output schema list.
     * @return array output schema list
     */
    public function getOutputSchemaList()
    {
        return $this->outputSchemaList;
    }

    /**
     * Returns the output schema.
     * @return string output schema
     */
    public function getOutputSchema()
    {
        return $this->outputSchema;
    }

    /**
     * Returns the result type list.
     * @return array result type list
     */
    public function getResultTypeList()
    {
        return $this->resultTypeList;
    }

    /**
     * Returns the result type.
     * @return string result type
     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     *
     * @param type $outputSchemaList
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AFindRecord
     */
    public function setOutputSchemaList($outputSchemaList)
    {
        $this->outputSchemaList = $outputSchemaList;
        return $this;
    }

    /**
     *
     * @param type $outputSchema
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AFindRecord
     */
    public function setOutputSchema($outputSchema)
    {
        $this->outputSchema = $outputSchema;
        return $this;
    }

    /**
     *
     * @param type $resultTypeList
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AFindRecord
     */
    public function setResultTypeList($resultTypeList)
    {
        $this->resultTypeList = $resultTypeList;
        return $this;
    }

    /**
     *
     * @param type $resultType
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AFindRecord
     */
    public function setResultType($resultType)
    {
        $this->resultType = $resultType;
        return $this;
    }

    public function getParameters()
    {
        return array(
            'outputSchema' => $this->outputSchemaList,
            'resultType' => $this->resultTypeList,
            'outputFormat' => $this->outputFormatList
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'outputSchema':
                if(self::isStringToSet($name, $value, $this->outputSchemaList, false)) {
                    $this->setOutputSchema($value);
                }
                break;
            case 'resultType':
                if(self::isStringToSet($name, $value, $this->resultTypeList, false)) {
                    $this->setResultType($value);
                }
                break;
            default:
                parent::setParameter($name, $value);
        }
    }
}