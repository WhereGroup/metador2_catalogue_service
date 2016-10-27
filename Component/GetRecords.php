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
    protected $constraintLanguageList;
    protected $typeNameList;
    protected $constraintList;

    /* Request parameters */
    protected $constraintLanguage;
    protected $typeNames;
    protected $startPosition;
    protected $maxRecords;
    protected $sortBy;
    protected $namespace;
    protected $requestId;
    protected $responseHandler;
    protected $elementName;
    protected $constraint;
    protected $deistributedSearch;
    protected $hopCount;

    /**
     * {@inheritdoc}
     */
    public function __construct(Csw $csw = null, $configuration = array())
    {
        parent::__construct($csw, $configuration);
        $this->name                   = 'GetRecords';
        $this->constraintLanguageList = $configuration['constraintLanguageList'];
        $this->typeNameList           = $configuration['typeNameList'];
        $this->typeName               = $this->typeNameList[0];
        $this->constraintList         = $configuration['constraintList'];

        $this->startPosition = 1; # default value s. xsd
        $this->maxRecords    = 10; # default value s. xsd
        $this->sortBy        = array();
//        $this->deistributedSearch = false;
//        $this->hopCount = 2; # default value s. xsd
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        unset(
            $this->constraintLanguageList, $this->typeNameList
        );
        parent::__destruct();
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

    public function getConstraintLanguageList()
    {
        return $this->constraintLanguageList;
    }

    public function getConstraintLanguage()
    {
        return $this->constraintLanguage;
    }

    public function getTypeNameList()
    {
        return $this->typeNameList;
    }

    public function getTypeName()
    {
        return $this->typeName;
    }

    public function setConstraintLanguageList($constraintLanguageList)
    {
        $this->constraintLanguageList = $constraintLanguageList;
        return $this;
    }

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
        return array_merge(parent::getParameters(),
            array(
            'typeNames' => $this->typeNameList,
            'CONSTRAINTLANGUAGE' => $this->constraintLanguageList
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        $constraints = array(
            'PostEncoding' => $this->postEncodingList
        );
        if (isset($this->constraintList['SupportedISOQueryables'])) {
            $constraints['SupportedISOQueryables'] = array_keys($this->constraintList['SupportedISOQueryables']);
        }
        return $constraints;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'typeNames':
                $typeNames = preg_split('/[\s,]/', $value);
                if ($this->isListAtList($name, $typeNames, $this->typeNameList, false)) {
                    $this->typeNames = $typeNames;
                }
                break;
            case 'startPosition':
                if ($value !== null) {
                    $this->startPosition = self::getGreaterThan($name, self::getPositiveInteger($name, $value), 0);
                }
                break;
            case 'maxRecords':
                if ($value !== null) {
                    $this->maxRecords = self::getGreaterThan($name, self::getPositiveInteger($name, $value), 0);
                }
                break;
            case 'constraintLanguage':
                if (self::isStringAtList($name, $value, $this->constraintLanguageList, false)) {
                    $this->constraintLanguage = $value;
                }
                break;
            case 'Constraint':
                $this->constraint = $value;
                break;
            case 'sortBy':
                $this->sortBy     = $value; # @TODO split and check if items supported/exist
                break;
            case 'namespace':
                // not yet implemented
                break;
            case 'requestId':
                $this->requestId = $value;
                break;
            case 'responseHandler':
//                  not yet implemented
                break;
            case 'elementName':
                // @TODO if exists
                break;
            case 'ResponseHandler':
//                 not yet implemented
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
    protected function render()
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
        $expr = null;
        if ($this->constraint) {
            $expr = $filter->generateFilter($qb, $name, $constarintsMap, $parameters, $this->constraint);
        }
        $qb->select('count(' . $name . '.id)');
        if ($expr) {
            // select only public metadata
            $num                       = count($parameters);
            $eq                        = new Expr\Comparison($name . '.public', '=', ':public' . $num);
            $parameters['public' . $num] = true;
            $expr                      = new Expr\Andx(array($expr, $eq));
            $qb->add('where', $expr)->setParameters($parameters);
        }
//        $query = $qb->getQuery();
        $matched  = $qb->getQuery()->getSingleScalarResult();
        $returned = $matched;
        $results  = array();
        if ($this->resultType === self::RESULTTYPE_RESULTS) {# || $this->resultType === self::RESULTTYPE_VALIDATE) {
            $qb->select($name);
            if ($expr) {
                $qb->add('where', $expr)
                    ->setParameters($parameters);
            }
            $qb->setFirstResult($this->startPosition - 1)
                ->setMaxResults($this->maxRecords);
            FilterCapabilities::generateSortBy($qb, $name, $constarintsMap, $this->sortBy);

            $results  = $qb->getQuery()->getResult();
            $returned = count($results);
        }
        # @TODO for self::RESULTTYPE_VALIDATE
        $time = new \DateTime();
        return $this->csw->getTemplating()->render(
                $this->templates[$this->getOutputFormat()],
                array(
                'timestamp' => $time->format('Y-m-d\TH:i:s'),
                'matched' => $matched,
                'returned' => $returned,
                'getrecords' => $this,
                'nextrecord' => $this->startPosition - 1,
                'records' => $results
                )
        );
    }
}