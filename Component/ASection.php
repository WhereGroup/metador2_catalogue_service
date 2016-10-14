<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of ASection
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class ASection
{
    protected $name;

    public function __construct($configuration)
    {
        
    }

    public function getName()
    {
        return $this->name;
    }
}