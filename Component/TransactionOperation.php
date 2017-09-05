<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;


use Plugins\WhereGroup\CatalogueServiceBundle\Component\Search\GmlFilterReader;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;

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
    private $filter;

    /**
     * TransactionOperation constructor.
     * @param $type
     * @param ExprHandler $expression
     */
    public function __construct($type, ExprHandler $expression)
    {
        $this->type = $type;
        $this->items = array();
        $this->filter = $expression;
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
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param \DOMElement $filter
     * @return $this
     */
    public function setFilter(\DOMElement $filter)
    {
        GmlFilterReader::read($filter, $this->filter);

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