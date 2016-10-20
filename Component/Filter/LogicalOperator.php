<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * Description of LogicalOperators
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class LogicalOperator extends AOperator
{

    public static $operators = array('And', 'Or', 'Not');

    public function useOperator(QueryBuilder $qb, $alias, $constarintsMap, &$parameters, $operator, $operands)
    {
        switch ($operator) {
            case 'And':
                $exprs = array();
                foreach ($operands as $key => $value) {
                    $exprs[] = $this->fc->generateFilter($qb, $alias, $constarintsMap, $parameters, array($key => $value));
                }
                return new Expr\Andx($exprs);
            case 'Or':
                $exprs = array();
                foreach ($operands as $key => $value) {
                    $exprs[] = $this->fc->generateFilter($qb, $alias, $constarintsMap, $parameters, array($key => $value));
                }
                return new Expr\Orx($exprs);
            case 'Not':
                $exprs = array();
                foreach ($operands as $key => $value) {
                    $exprs[] = $this->fc->generateFilter($qb, $alias, $constarintsMap, $parameters, array($key => $value));
                    break;
                }
                // @TODO for multiple not ???
                $expr = new Expr();
                return $expr->not($exprs[0]);
            default:
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }
}