<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\ASection;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * Description of FilterCapabilities
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class FilterCapabilities extends ASection
{
    protected $name = 'FilterCapabilities';
    protected $data;
    protected $all;
    protected $comparison;
    protected $logical;
    protected $spatial;
    protected $inited;

    public function __construct($configuration = array())
    {
        // @TODO make capabilities configurable???
        $this->data   = array(
            'spatial_Capabilities' => array(
                'geometryOperands' => SpatialCapabilities::$operands,
                'spatialOperators' => SpatialCapabilities::$operators
            ),
            'scalar_Capabilities' => array(
                'logicalOperators' => true,
                'comparisonOperators' => array_keys(ComparisonOperator::$operators)
            ),
            'id_Capabilities' => array('EID', 'FID')
        );
        $this->inited = false;
    }

    public function getData()
    {
        return $this->data;
    }

    private function initOperators()
    {
        if (!$this->inited) {
            $this->all = array();

            $this->comparison = new ComparisonOperator($this);
            foreach (ComparisonOperator::$operators as $key => $value) {
                $this->all[$value] = $this->comparison;
            }

            $this->logical = new LogicalOperator($this);
            foreach (LogicalOperator::$operators as $value) {
                $this->all[$value] = $this->logical;
            }

            $this->spatial = new SpatialCapabilities($this);
            foreach (SpatialCapabilities::$operators as $value) {
                $this->all[$value] = $this->spatial;
            }
            $this->inited = true;
        }
    }

    public function generateFilter(QueryBuilder $qb, $alias, $constarintsMap, &$parameters, $filter)
    {
        $this->initOperators();
        foreach ($filter as $key => $value) {
            if (is_integer($key)) {
                return $this->generateFilter($qb, $alias, $constarintsMap, $parameters, $value);
            } else
                try {
                    return $this->getOperator($key)->useOperator($qb, $alias, $constarintsMap, $parameters, $key,
                            $value);
                } catch (\Exception $e) {
                    throw $e instanceof CswException ? $e : new CswException('constraint',
                        CswException::InvalidParameterValue);
                }
        }
    }

    private function getOperator($name)
    {
        if (isset($this->all[$name])) {
            return $this->all[$name];
        } else {
            return null;
        }
    }

    public static function generateSortBy(QueryBuilder &$qb, $alias, $constarintsMap, $sortBy)
    {
        $sortOrder = array('ASC' => 'ASC', 'DESC' => 'DESC');
        try {
            foreach ($sortBy as $item) {
                $name = $alias . '.' . $constarintsMap[$item['SortProperty']['children'][0]['PropertyName']['VALUE']];
                if (isset($item['SortProperty']['children'][1])) {
                    $qb->addOrderBy($name, $sortOrder[$item['SortProperty']['children'][1]['SortOrder']['VALUE']]);
                } else {
                    $qb->addOrderBy($name);
                }
            }
        } catch (\Exception $e) {
            throw new CswException('sortBy', CswException::InvalidParameterValue);
        }
    }
}