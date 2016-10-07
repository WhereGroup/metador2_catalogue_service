<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of AParameterHandler
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class AParameterHandler
{
    abstract public function getParameter($name, $caseSensitive = false);
    abstract public function getOperation();
//    abstract public function parseParameters(AOperation $peration);
}