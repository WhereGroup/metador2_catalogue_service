<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of OperationsMetadata
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class OperationsMetadata extends ASection
{
    protected $name = 'OperationsMetadata';

    protected $data;

    public function __construct($configuration)
    {
        $this->data = $configuration;
        unset($this->data['class']);
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}