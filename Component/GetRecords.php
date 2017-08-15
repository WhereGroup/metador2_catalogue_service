<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

//use Doctrine\ORM\Query\Expr\Select;
//use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter\FilterCapabilities;

/**
 * The class GetRecords is a representation of the OGC CSW GetCapabilities operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetRecords extends AFindRecord
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
//        '/csw:GetRecords/csw:Query/csw:Constraint/csw:CqlText/text()' => 'constraint', # @TODO ???? check xpath
        '/csw:GetRecords/csw:Query/csw:Constraint' => 'Constraint',
        '/csw:GetRecords/csw:Query/ogc:SortBy' => 'sortBy',
////        'namespace',
        '/csw:GetRecords/csw:RequestId/text()' => 'requestId',
//        '/csw:GetRecords/csw:ResponseHandler' => 'responseHandler',
////        'deistributedSearch' for GET
//        '/csw:GetRecords/csw:DistributedSearch/@hopCount' => 'hopCount',
    );
//    protected $constraintLanguageList;
//    protected $typeNameList;
//    protected $constraintList;
//    protected $geometryQueryables;
//    protected $resultTypeList;

    /* Request parameters */
    protected $typeNames;
    protected $startPosition;
    protected $maxRecords;
    protected $sortBy;
    protected $constraint;
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
    public function __construct(\Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity = null)
    {
        parent::__construct($entity);
        $this->resultType             = self::RESULTTYPE_HITS;
        $this->typeNames               = array('gmd:MD_Metadata');
        $this->constraintLanguage = 'FILTER';

        $this->startPosition = 1; # default value s. xsd
        $this->maxRecords    = 10; # default value s. xsd
        $this->sortBy        = array();
//        $this->deistributedSearch = false;
//        $this->hopCount = 2; # default value s. xsd
    }

    /**
     * {@inheritdoc}
     */
    public static function getGETParameterMap()
    {
        return array_merge(array('constraintLanguage'), array_values(self::$parameterMap));
    }

    /**
     * {@inheritdoc}
     */
    public static function getPOSTParameterMap()
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
     * @param $typeNames
     * @return $this
     */
    public function setTypeNames($typeNames)
    {
        $typeNameArr = preg_split('/[\s,]/', $typeNames);
        $this->isListAtList('typeNames', $typeNameArr, $this->typeNames, false); # only check
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
     */
    public function setStartPosition($startPosition)
    {
        if ($startPosition !== null) {
            $this->startPosition = self::getGreaterThan('startPosition',
                self::getPositiveInteger(startPosition, $startPosition), 0);
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
     */
    public function setMaxRecords($maxRecords)
    {
        if ($maxRecords !== null) {
            $this->maxRecords = self::getGreaterThan('maxRecords',
                self::getPositiveInteger('maxRecords', $maxRecords), 0);
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
     * @return mixed
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * @param $constraint
     * @return $this
     */
    public function setConstraint($constraint)
    {
        $this->constraint = $constraint;
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
     */
    public function setConstraintLanguage($constraintLanguage)
    {
        self::isStringAtList('constraintLanguage', $constraintLanguage, array($this->constraintLanguage), false);
        return $this;
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
     * @param string $resultType
     * @return $this
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
    protected function validateParameter()
    {
        // check contstarint and constraintLanguage
        if ($this->constraint) {
            if (is_array($this->constraint)) {
                if (isset($this->constraint[0]['Filter'])) { #
                    if (self::isStringAtList('Constraint', 'FILTER', $this->constraintLanguageList, false)) {
                        $this->constraintLanguage = 'FILTER';
                        $this->constraint         = $this->constraint[0]['Filter']['children']; # @TODO check filter
                    }
                } elseif (isset($this->constraint[0]['CqlText'])) { #
                    if (self::isStringAtList('Constraint', 'CQL', $this->constraintLanguageList, false)) {
                        $this->constraintLanguage = 'CQL';
                        $cqlStr                   = $this->constraint[0]['CqlText']['VALUE'];
                        // @TODO parse $cqlStr to "filter" array
                        $this->constraint         = $this->constraint[0]['CqlText']['VALUE']; # @TODO check filter
                    }
                } else {
                    $this->addCswException('constraint', CswException::InvalidParameterValue);
                }
            } elseif (is_string($this->constraint)) {
                if ($this->constraintLanguage) {
                    if ($this->constraintLanguage === 'FILTER') {
                        $constraint       = Parameter\SimpleSaxHandler::toArray(
                                '<csw:Filter>' . $this->constraint . '</csw:Filter>',
                                array('/csw:Filter' => 'Constraint'));
                        $this->constraint = $constraint['Constraint'];
                    } elseif ($this->constraintLanguage === 'CSL') {
                        $this->addCswException('CSL not yet implemented', CswException::NoApplicableCode);
                    } else {
                        $this->addCswException($this->constraintLanguage . ' not yet implemented',
                            CswException::NoApplicableCode);
                    }
                } else {
                    $this->addCswException('constraintLanguage', CswException::MissingParameterValue);
                }
            } else {
                $this->addCswException('constraint', CswException::InvalidParameterValue);
            }
        }
        return parent::validateParameter();
    }

    /**
     * {@inheritdoc}
     */
    protected function render($templating)
    {
        $name           = 'm';
        /** @var QueryBuilder $qb */
        $qb             = $this->csw->getMetadata()->getQueryBuilder($name);
        $filter         = new FilterCapabilities();
        $parameters     = array();
        $constarintsMap = array();
        foreach ($this->constraintList as $key => $value) {
            $constarintsMap = array_merge_recursive($constarintsMap, $value);
        }
        $constarintsMap = array_merge_recursive(
            isset($this->geometryQueryables) ? $this->geometryQueryables : array(), $constarintsMap);

        $num                         = count($parameters);
        $finalExpr                   = new Expr\Comparison($name . '.public', '=', ':public' . $num);
        $parameters['public' . $num] = true;
        $filterExpr                  = null;
        if ($this->constraint) {
            $filterExpr = $filter->generateFilter($qb, $name, $constarintsMap, $parameters, $this->constraint);
        }
        $qb->select('count(' . $name . '.id)');
        if ($filterExpr) {
            $finalExpr = new Expr\Andx(array($filterExpr, $finalExpr));
        }
        $qb->add('where', $finalExpr)->setParameters($parameters);
        $query    = $qb->getQuery();
        $matched  = $qb->getQuery()->getSingleScalarResult();
        $returned = $matched;
        $results  = array();
        if ($this->resultType === self::RESULTTYPE_RESULTS) {# || $this->resultType === self::RESULTTYPE_VALIDATE) {
            $qb->select($name);
            $qb->add('where', $finalExpr)->setParameters($parameters);
            $qb->setFirstResult($this->startPosition - 1)
                ->setMaxResults($this->maxRecords);
            FilterCapabilities::generateSortBy($qb, $name, $constarintsMap, $this->sortBy);

            $results  = $qb->getQuery()->getResult();
            $returned = count($results);
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<csw:GetRecordsResponse xmlns:ows=\"http://www.opengis.net/ows\"  xmlns:csw=\"http://www.opengis.net/cat/csw/2.0.2\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.opengis.net/cat/csw/2.0.2 http://schemas.opengis.net/csw/2.0.2/CSW-discovery.xsd\">";

        $time = new \DateTime();
        $timestamp = $time->format('Y-m-d\TH:i:s');

        if (isset($this->requestId)) {
            $xml .= "\n<csw:RequestId>" . $timestamp . "</csw:RequestId>";
        }

        $xml .= "\n<csw:SearchStatus timestamp=\"" . $timestamp . "\" />
<csw:SearchResults numberOfRecordsMatched=\"" . $matched . "\" numberOfRecordsReturned=\"" . $returned . "\" elementSet=\"" . $this->elementSetName . "\" nextRecord=\"" . ($this->startPosition - 1) . "\">";

        foreach ($results as $record) {
            $className = $this->csw->container->get('metador_plugin')->getPluginClassName($record->getProfile());
            $xml .= "\n" . $this->csw->getTemplating()->render(
                $className . ":Export:metadata.xml.twig",
                array('p' => $record->getObject())
            );
        }

        $xml .= "\n</csw:SearchResults>
</csw:GetRecordsResponse>";

        return $xml;
    }
}
