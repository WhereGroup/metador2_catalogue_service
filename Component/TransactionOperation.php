<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Search\GmlFilterReader;
use WhereGroup\CoreBundle\Component\Search\Expression;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;

/**
 * Class TransactionOperation
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 * @author Paul Schmidt <panadium@gmx.de>
 */
class TransactionOperation
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var string
     */
    private $handle;

    /**
     * @var array
     */
    private $items;

    /**
     * @var ExprHandler
     */
    private $exprHandler;

    /**
     * @var Expression
     */
    private $constraint;

    /**
     * TransactionOperation constructor.
     * @param $type
     * @param ExprHandler $exprHandler
     */
    public function __construct($type, ExprHandler $exprHandler)
    {
        $this->type = $type;
        $this->items = [];
        $this->exprHandler = $exprHandler;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * @param mixed $typeName
     * @return TransactionOperation
     */
    public function setTypeName($typeName)
    {
        $this->typeName = $typeName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param mixed $handle
     * @return TransactionOperation
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return TransactionOperation
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return ExprHandler
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * @param \DOMElement $filter
     * @return $this
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public function setConstraint(\DOMElement $filter)
    {
        $this->constraint = GmlFilterReader::readFromCsw($filter, $this->exprHandler);

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'handle':
                $this->setHandle($value);
                break;
            case 'typeName':
                $this->setTypeName($value);
                break;
            default:
                throw new \Exception('Transaction: not defined parameter:'.$name);
        }
    }

    public function validateParameter()
    {
        ;
    }
}
