<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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