<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.17
 * Time: 17:21
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;


class TransactionAction
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
     * @var string
     */
    private $filter;

    /**
     * TransactionType constructor.
     * @param string $type
     */
    public function __construct($type, $filter = null)
    {
        $this->type = $type;
        $this->items = array();
        $this->filter = $filter;
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
     * @return TransactionAction
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
     * @return TransactionAction
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
     * @return TransactionAction
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
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

    }

}