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
                'comparisonOperators' => ComparisonOperator::$operators
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
            foreach (ComparisonOperator::$operators as $value) {
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
            try {
                $name = preg_replace('/^PropertyIs/', '', $key); # @TODO
                return $this->getOperator($name)->useOperator($qb, $alias, $constarintsMap, $parameters, $name, $value);
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
//        } elseif (isset($this->all[preg_replace('/^PropertyIs/', '', $name)])) {
//            return $this->all[preg_replace('/^PropertyIs/', '', $name)];
        } else {
            return null;
        }
    }
}