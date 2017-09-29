<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use WhereGroup\CoreBundle\Component\Search\Expression;

/**
 * The class GetRecordById is a representation of the OGC CSW GetCapabilities operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetRecordById extends FindRecord
{
    /**
     * {@inheritdoc}
     */
    public static $parameterMap = array(
        'version' => '/csw:GetRecordById/@version',
        'service' => '/csw:GetRecordById/@service',
        'outputSchema' => '/csw:GetRecordById/@outputSchema',
        'outputFormat' => '/csw:GetRecordById/@outputFormat',
        'elementSetName' => '/csw:GetRecordById/csw:ElementSetName/text()',
        'id' => '/csw:GetRecordById/csw:Id/text()',
    );

    protected $outputSchema;
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function setConstraint($constraintContent)
    {
        $parameters = array();
        $this->constraint = new Expression(
            $this->exprHandler->in('Identifier', $constraintContent, $parameters),
            $parameters
        );
    }

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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function setId($id)
    {
        if ($id && is_string($id)) {
            $this->id = $this->parseCsl($id);
        } elseif ($id && is_array($id)) {
            $this->id = $id;
        }
        $this->setConstraint($this->id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'id':
                $this->setId($value);
                break;
            default:
                parent::setParameter($name, $value);
        }
    }
}
