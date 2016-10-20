<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * Description of ComparisonOperators
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class ComparisonOperator extends AOperator
{
    const DOCTRINE_WILDCARD   = '%';
    const DOCTRINE_SINGLECHAR = '_';
    const DOCTRINE_ESCAPECHAR = '\\';

    public static $operators = array(
        'Between',
        'EqualTo',
        'NotEqualTo',
        'GreaterThan',
        'GreaterThanEqualTo',
        'LessThan',
        'LessThanEqualTo',
        'Like',
        'NullCheck'
    );

    private function addEscape($character)
    {
        if (strpos('!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~', $character) !== false) {
            return '\\' . $character;
        } else {
            return $character;
        }
    }

    private function getRegex($escape, $character)
    {
        $first  = $this->addEscape($escape);
        $second = $this->addEscape($character);
        return '/(?<!' . $first . ')(' . $second . ')/';
    }

    public function useOperator(QueryBuilder $qb, $alias, $constarintsMap, &$parameters, $operator, $operands)
    {
        switch ($operator) {
            case 'Between':
                $attribute = $this->getFromMap($constarintsMap, $operands['ValueReference']['VALUE']);
                $lower     = $this->addParameter($parameters, $attribute, $operands['LowerBoundary']['Literal']['VALUE']);
                $upper     = $this->addParameter($parameters, $attribute, $operands['UpperBoundary']['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->between($name, $lower, $upper);
            case 'EqualTo':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value     = $this->addParameter($parameters, $attribute, $operands['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->eq($name, $value);
            case 'NotEqualTo':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value     = $this->addParameter($parameters, $attribute, $operands['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->neq($name, $value);
            case 'Like':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value    = $operands['Literal']['VALUE'];
                $escape    = $operands['escapeChar'];
                $wildcard = $operands['wildCard'];
                $singlechar = $operands['singleChar'];
                #repalce wildCard
                $value   = preg_replace($this->getRegex($escape, $wildcard), self::DOCTRINE_WILDCARD, $value);
                #repalce singleChar
                $value   = preg_replace($this->getRegex($escape, $singlechar), self::DOCTRINE_SINGLECHAR, $value);
                #repalce escape
                $value   = preg_replace($this->getRegex($escape, $escape), self::DOCTRINE_ESCAPECHAR, $value);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->like($name, $qb->expr()->literal($value));
            case 'GreaterThan':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value     = $this->addParameter($parameters, $attribute, $operands['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->gt($name, $value);
            case 'GreaterThanEqualTo':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value     = $this->addParameter($parameters, $attribute, $operands['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->gte($name, $value);
            case 'LessThan':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value     = $this->addParameter($parameters, $attribute, $operands['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->lt($name, $value);
            case 'LessThanEqualTo':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $value     = $this->addParameter($parameters, $attribute, $operands['Literal']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->lte($name, $value);
            case 'NullCheck':
                $attribute = $this->getFromMap($constarintsMap, $operands['PropertyName']['VALUE']);
                $name      = $this->getName($alias, $attribute);
                $expr = new Expr();
                return $expr->isNull($name);
            default:
                throw new CswException('filter', CswException::NoApplicableCode);
        }
    }
}