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
//        'Disjoint',
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
//                return;
            case 'Intersects':
                $map  = $constarintsMap[$operands['children'][0]['PropertyName']['VALUE']];
                $mapA = array(
                    'wA' => $this->getFromMap($map, 'bboxw'),
                    'sA' => $this->getFromMap($map, 'bboxs'),
                    'eA' => $this->getFromMap($map, 'bboxe'),
                    'nA' => $this->getFromMap($map, 'bboxn')
                );
                return $this->exprForOperand($alias, $mapA, $parameters, $operands['children'][1]);
            default:
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }

    private function exprForOperand($alias, $mapA, &$parameters, $operand)
    {
        $name = null;
        foreach ($operand as $name => $value) {
            break;
        }
        switch ($name) {
            case 'Envelope':
                $lc   = preg_split('/[ ,]/', $value['children'][0]['lowerCorner']['VALUE']);
                $uc   = preg_split('/[ ,]/', $value['children'][1]['upperCorner']['VALUE']);
                $geom = array(
                    "w" => floatval($lc[0]),
                    "s" => floatval($lc[1]),
                    "e" => floatval($uc[0]),
                    "n" => floatval($uc[1]),
                );
                $orX       = new Expr\Orx(
                    array(
                        $this->exprMathBetween($alias, $parameters, $mapA['wA'], $mapA['eA'], $geom['w']),
                        $this->exprMathBetween($alias, $parameters, $mapA['wA'], $mapA['eA'], $geom['e'])
                    )
                );
                $orY     = new Expr\Orx(
                    array(
                        $this->exprMathBetween($alias, $parameters, $mapA['sA'], $mapA['nA'], $geom['s']),
                        $this->exprMathBetween($alias, $parameters, $mapA['sA'], $mapA['nA'], $geom['n'])
                    )
                );
                $finalExpr = new Expr\Andx(array($orX, $orY));
                return $finalExpr;
            case 'Point':
                $point     = preg_split('/[ ,]/', $value['children'][0]['pos']['VALUE']);
                $geom = array(
                    'x' => floatval($point[0]),
                    'y' => floatval($point[1])
                );
                $andX       = new Expr\Andx(
                    array(
                        $this->exprMathBetween($alias, $parameters, $mapA['wA'], $mapA['eA'], $geom['x']),
                        $this->exprMathBetween($alias, $parameters, $mapA['sA'], $mapA['nA'], $geom['y'])
                    )
                );
                return $andX;
            default :
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }

    private function exprMathBetween($alias, &$parameters, $propMin, $propMax, $tocheck, $andEq = true)
    {
        return new Expr\Andx(array(
            new Expr\Comparison($this->getName($alias, $propMin), $andEq ? '<=' : '<',
                $this->addParameter($parameters, $propMin, $tocheck)),
            new Expr\Comparison($this->getName($alias, $propMax), $andEq ? '>=' : '>',
                $this->addParameter($parameters, $propMax, $tocheck))
        ));
    }
}