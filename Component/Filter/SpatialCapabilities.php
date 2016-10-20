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
class SpatialCapabilities# extends AOperator
{
    public static $operators = array(
//        'BBOX',
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

    public static $operands = array(
        'gml:Envelope',
//        'gml:Point',
//        'gml:LineString',
//        'gml:Polygon'
    );

    public function useOperator(QueryBuilder $qb, $alias, $constarintsMap, &$parameters, $operator, $operands)
    {
        switch ($operator) {
//            case 'BBOX':
//                return;
            case 'Intersects':
//                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
//                $name      = $this->getName($alias, $attribute);
//                $expr = new Expr();
                return ;
            default:
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }

}