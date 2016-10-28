<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter;

use Doctrine\ORM\QueryBuilder;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * Description of AOperator
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class AOperator
{
    public static $operators = array();
    
    protected $fc;

    public function __construct(FilterCapabilities $fc)
    {
        $this->fc = $fc;
    }

    public function getFromMap($map, $key)
    {
        try {
            if(isset($map[$key])){
                return$map[$key];
            } else {
                throw new CswException($key, CswException::InvalidParameterValue);
            }
        } catch (\Exception $ex) {
            throw new CswException($key, CswException::InvalidParameterValue);
        }
    }

    public function addParameter(&$parameters, $attribute, $value)
    {
        $name = $attribute . count($parameters);
        $parameters[$name] = $value;
        return  ':' . $name;
    }

    public function getName ($alias, $name)
    {
        return $alias . '.' . $name;
    }


    abstract public function useOperator(QueryBuilder $qb, $alias, $constarintsMap, &$parameters, $operator, $operands);
}