<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * Description of SpatialOperators
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class SpatialCapabilities extends AOperator
{
    public static $operators = array(
        'BBOX',
//        'Beyond',
//        'Contains',
//        'Crosses',
        'Disjoint',
//        'DWithin',
//        'Equals',
        'Intersects',
//        'Overlaps',
//        'Touches',
//        'Within'
    );
    public static $operands  = array(
        'gml:Envelope',
        'gml:Point',
//        'gml:LineString',
//        'gml:Polygon'
    );

    public function useOperator(QueryBuilder $qb, $alias, $constarintsMap, &$parameters, $operator, $operands)
    {
        switch ($operator) {
            case 'BBOX':
            case 'Intersects':
                $props = $this->getPropsMap($constarintsMap, $operands);
                return $this->exprForIntersects($alias, $props, $this->getGeometry($operands['children'][1]), $parameters);
            case 'Disjoint':
                $props = $this->getPropsMap($constarintsMap, $operands);
                $expr = new Expr();
                return $expr->not(
                    $this->exprForIntersects($alias, $props, $this->getGeometry($operands['children'][1]), $parameters));
            default:
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }

    private function getPropsMap($constarintsMap, $operands)
    {
        $map  = $constarintsMap[$operands['children'][0]['PropertyName']['VALUE']];
        return array(
                    'w' => $this->getFromMap($map, 'bboxw'),
                    's' => $this->getFromMap($map, 'bboxs'),
                    'e' => $this->getFromMap($map, 'bboxe'),
                    'n' => $this->getFromMap($map, 'bboxn')
                );
    }

    private function getGeometry($operand)
    {
        $name = null;
        foreach ($operand as $name => $value) {
            break;
        }
        switch ($name) {
            case 'Envelope':
                $lc    = preg_split('/[ ,]/', $value['children'][0]['lowerCorner']['VALUE']);
                $uc    = preg_split('/[ ,]/', $value['children'][1]['upperCorner']['VALUE']);
                $geom  = array(
                    "w" => floatval($lc[0]),
                    "s" => floatval($lc[1]),
                    "e" => floatval($uc[0]),
                    "n" => floatval($uc[1]),
                );
                return array('Envelope' => $geom);
            case 'Point':
                $point = preg_split('/[ ,]/', $value['children'][0]['pos']['VALUE']);
                $geom  = array(
                    'x' => floatval($point[0]),
                    'y' => floatval($point[1])
                );
                return array('Point' => $geom);
            default :
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }

    private function exprForIntersects($alias, $props, $geom, &$parameters)
    {
        $name = null;
        foreach ($geom as $name => $geom) {
            break;
        }
        switch ($name) {
            case 'Envelope':
                return new Expr\Andx(array(
                    new Expr\Comparison($this->getName($alias, $props['e']), '>=',
                        $this->addParameter($parameters, $props['e'], $geom['w'])),
                    new Expr\Comparison($this->getName($alias, $props['w']), '<=',
                        $this->addParameter($parameters, $props['w'], $geom['e'])),
                    new Expr\Comparison($this->getName($alias, $props['n']), '>=',
                        $this->addParameter($parameters, $props['n'], $geom['s'])),
                    new Expr\Comparison($this->getName($alias, $props['s']), '<=',
                        $this->addParameter($parameters, $props['s'], $geom['n']))
                ));
            case 'Point':
                return new Expr\Andx(array(
                    new Expr\Comparison($this->getName($alias, $props['e']), '>=',
                        $this->addParameter($parameters, $props['e'], $geom['x'])),
                    new Expr\Comparison($this->getName($alias, $props['w']), '<=',
                        $this->addParameter($parameters, $props['w'], $geom['x'])),
                    new Expr\Comparison($this->getName($alias, $props['n']), '>=',
                        $this->addParameter($parameters, $props['n'], $geom['y'])),
                    new Expr\Comparison($this->getName($alias, $props['s']), '<=',
                        $this->addParameter($parameters, $props['s'], $geom['y']))
                ));
            default :
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }
}