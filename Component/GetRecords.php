<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\Query\Expr\From;
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
        '/csw:GetRecords/csw:Query/csw:ElementName/text()' => 'elementName',
        '/csw:GetRecords/csw:Query/csw:Constraint/csw:CqlText/text()' => 'constraint', # @TODO ???? check xpath
        '/csw:GetRecords/csw:Query/csw:Constraint/ogc:Filter' => 'constraint',
//        '/csw:GetRecords/csw:Query/ogc:SortBy/ogc:SortProperty' => array(
//            './ogc:PropertyName' => 'sortBy',
//            './ogc:SortProperty' => 'sortBy' #DESC ASC
//        ),
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
    protected $elementSetName; #???
    protected $elementName; #
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
        $this->constraintLanguage     = $this->constraintLanguageList[0];
        $this->typeNameList           = $configuration['typeNameList'];
        $this->typeName               = $this->typeNameList[0];
        $this->constraintList         = $configuration['constraintList'];

        $this->startPosition = 1; # default value s. xsd
        $this->maxRecords    = 10; # default value s. xsd
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
        return array_values(self::$parameterMap);
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
//        protected $constraintLanguage;
//        protected $typeNames;
//
//
//
//        protected $namespace;
//
//        protected $requestId;
//        protected $responseHandler;
//
//        protected $elementSetName;#???
//        protected $elementName;#
//
//        protected $constraint;
//        protected $deistributedSearch;
//        protected $hopCount;

        switch ($name) {
            case 'constraintLanguage':
                if (self::isStringToSet($name, $value, $this->constraintLanguageList, false)) {
                    $this->constraintLanguage = $value;
                }
                break;
            case 'typeNames':
                if (self::isStringToSet($name, $value, $this->typeNameList, false)) {
                    $this->typeName = $value;
                }
                break;
            case 'startPosition':
                $this->startPosition = self::getGreaterThan($name, self::getPositiveInteger($name, $value), 0);
                break;
            case 'maxRecords':
                $this->maxRecords    = self::getGreaterThan($name, self::getPositiveInteger($name, $value), 0);
                break;
            case 'sortBy':
                $this->sortBy        = $value; # @TODO split and check if items supported/exist
                break;
            case 'constraint':
                $this->constraint    = $value; # @TODO check filter
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    protected function render()
    {
        $name = 'm';
        $qb             = $this->csw->getMetadata()->getQueryBuilder($name);
        $filter         = new FilterCapabilities();
        $parameters     = array();
        $constarintsMap = array();
        foreach ($this->constraintList as $key => $value) {
            $constarintsMap = array_merge_recursive($constarintsMap, $value);
        }
        $expr = $filter->generateFilter($qb, $name, $constarintsMap, $parameters, $this->constraint);
        $qb->select('count(' . $name . '.id)')
            ->add('where', $expr)
            ->setParameters($parameters);
        $query = $qb->getQuery();
        $matched = $qb->getQuery()->getSingleScalarResult();#->getResult();#->getSingleScalarResult();#->getResult()

        $qb->select($name)
            ->add('where', $expr)
            ->setFirstResult($this->startPosition - 1)
            ->setMaxResults($this->maxRecords)
            ->setParameters($parameters);
        $query = $qb->getQuery();
        $results = $qb->getQuery()->getResult();
        $time    = new \DateTime();
        return $this->csw->getTemplating()->render(
                $this->templates[$this->getOutputFormat()],
                array(
                'timestamp' => $time->format('Y-m-d\TH:i:s'),
                'matched' => $matched,
                'returned' => count($results),
                'nextrecord' => $this->startPosition - 1,
                'records' => $results
                )
        );
    }
}