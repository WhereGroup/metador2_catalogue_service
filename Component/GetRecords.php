<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Search\GmlFilterReader;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;

/**
 * Class GetRecords
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 * @author Paul Schmidt <panadium@gmx.de>
 */
class GetRecords extends FindRecord
{
    /**
     * {@inheritdoc}
     */
    protected static $parameterMap = array(
        '/csw:GetRecords/@version' => 'version',
        '/csw:GetRecords/@service' => 'service',
        '/csw:GetRecords/@outputSchema' => 'outputSchema',
        '/csw:GetRecords/@outputFormat' => 'outputFormat',
        '/csw:GetRecords/@resultType' => 'resultType',
        '/csw:GetRecords/@startPosition' => 'startPosition',
        '/csw:GetRecords/@maxRecords' => 'maxRecords',
//        '' => 'constraintLanguage',
        '/csw:GetRecords/csw:Query/@typeNames' => 'typeNames',
        '/csw:GetRecords/csw:Query/csw:ElementSetName/text()' => 'elementSetName',
        '/csw:GetRecords/csw:Query/csw:ElementName/text()' => 'elementName', # multiple?
//        '/csw:GetRecords/csw:Query/csw:Constraint/csw:CqlText/text()' => 'constraint', // constraintLanguage CQL
        '/csw:GetRecords/csw:Query/csw:Constraint/ogc:Filter' => 'Constraint',
        '/csw:GetRecords/csw:Query/ogc:SortBy' => 'sortBy',
////        'namespace',
        '/csw:GetRecords/csw:RequestId/text()' => 'requestId',
//        '/csw:GetRecords/csw:ResponseHandler' => 'responseHandler',
////        'deistributedSearch' for GET
//        '/csw:GetRecords/csw:DistributedSearch/@hopCount' => 'hopCount',
    );

    /* @var array supported typenames */
    protected static $TYPENAMES = ['gmd:MD_Metadata'];

    /* Request parameters */
    protected $typeNames;
    protected $startPosition;
    protected $maxRecords;
    protected $sortBy;
    protected $constraintLanguage;
    protected $resultType;
    protected $requestId;

    protected $namespace;
    protected $responseHandler;
    protected $elementName;
    protected $deistributedSearch;
    protected $hopCount;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity = null,
        ExprHandler $exprHandler
    ) {
        parent::__construct($entity, $exprHandler);
        $this->resultType = self::RESULTTYPE_HITS;
        $this->constraintLanguage = 'FILTER';

        $this->startPosition = 1; # default value s. xsd
        $this->maxRecords = 10; # default value s. xsd
        $this->sortBy = array();
//        $this->deistributedSearch = false;
//        $this->hopCount = 2; # default value s. xsd
    }

    /**
     * {@inheritdoc}
     */
    public function getGETParameterMap()
    {
        return array_merge(array('constraintLanguage'), array_values(self::$parameterMap));
    }

    /**
     * {@inheritdoc}
     */
    public function getPOSTParameterMap()
    {
        return self::$parameterMap;
    }

    /**
     * @return array
     */
    public function getTypeNames()
    {
        return $this->typeNames;
    }

    /**
     * @param string $typeNames
     * @return $this
     * @throws CswException
     */
    public function setTypeNames($typeNames)
    {
        $typeNameArr = preg_split('/[\s,]/', $typeNames);
        $this->isListAtList('typeNames', $typeNameArr, self::$TYPENAMES, false); # only check
        $this->typeNames = $typeNames;

        return $this;
    }

    /**
     * @return int
     */
    public function getStartPosition()
    {
        return $this->startPosition;
    }

    /**
     * @param mixed $startPosition
     * @return $this
     * @throws CswException
     */
    public function setStartPosition($startPosition)
    {
        if ($startPosition !== null) {
            $this->startPosition = self::getGreaterThan(
                'startPosition',
                self::getPositiveInteger('startPosition', $startPosition),
                0
            );
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxRecords()
    {
        return $this->maxRecords;
    }

    /**
     * @param mixed $maxRecords
     * @return $this
     * @throws CswException
     */
    public function setMaxRecords($maxRecords)
    {
        if ($maxRecords !== null) {
            $this->maxRecords = self::getGreaterThan(
                'maxRecords',
                self::getPositiveInteger('maxRecords', $maxRecords),
                0
            );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param array $sortBy
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy; # @TODO split and check if items supported/exist
    }

    /**
     * @param \DOMElement|string $constraintContent
     * @return $this
     * @throws CswException
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function setConstraint($constraintContent)
    {
        // only xml Filter is supported
        if (is_string($constraintContent)) {
            $xml = '<?xml version="1.0" >\n<csw:Filter>'.$constraintContent.'</csw:Filter>';
            $dom = new \DOMDocument();
            if (!@$dom->loadXML($xml, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_XINCLUDE)) {
                throw new CswException('filter', CswException::ParsingError);
            }
            if ($constraintContent !== null) {
                $this->constraint = GmlFilterReader::read($dom->documentElement, $this->exprHandler);
            }
        } else {
            if ($constraintContent !== null) {
                $this->constraint = GmlFilterReader::read($constraintContent, $this->exprHandler);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getConstraintLanguage()
    {
        return $this->constraintLanguage;
    }

    /**
     * @param $constraintLanguage
     * @return $this
     * @throws CswException
     */
    public function setConstraintLanguage($constraintLanguage)
    {
        self::isStringAtList('constraintLanguage', $constraintLanguage, array($this->constraintLanguage), false);

        return $this;
    }

    /**
     * Returns the result type.
     * @return string
     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     * @param string $resultType
     * @return $this
     * @throws CswException
     */
    public function setResultType($resultType)
    {
        if (self::isStringAtList('resultType', $resultType, self::RESULTTYPE, false)) {
            $this->resultType = $resultType;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     * @return GetRecords
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'resultType':
                $this->setResultType($value);
                break;
            case 'typeNames':
                $this->setTypeNames($value);
                break;
            case 'startPosition':
                $this->setStartPosition($value);
                break;
            case 'maxRecords':
                $this->setMaxRecords($value);
                break;
            case 'constraintLanguage':
                $this->setConstraintLanguage($value);
                break;
            case 'Constraint':
                $this->setConstraint($value);
                break;
            case 'sortBy':
                $this->setSortBy($value);
                break;
            case 'requestId':
                $this->setRequestId($value);
                break;
            case 'namespace':
                // not yet implemented
                break;
            case 'responseHandler':
//                  not yet implemented
                break;
            case 'ResponseHandler':
//                 not yet implemented
                break;
            case 'elementName':
                // @TODO if exists
                break;
            case 'deistributedSearch':
//                not yet implemented
//                $this->deistributedSearch = $value; # check if $value is a boolean
                break;
            case 'hopCount':
//                not yet implemented
//                $this->hopCount = $value; #check if $value is a positive integer and deistributedSearch is requested.
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateParameter()
    {
        if (!$this->typeNames) {
            $this->addCswException('typeNames', CswException::MISSINGPARAMETERVALUE);
        }

        return parent::validateParameter();
    }
}
