<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of ServiceProvider
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class ServiceProvider extends ASection
{
    protected $name = 'ServiceProvider';

    //@TODO inplement variables ???
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