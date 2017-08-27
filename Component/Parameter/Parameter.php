<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.17
 * Time: 15:15
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Operation;

/**
 * Interface Parameter
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter
 * @author Paul Schmidt <panadium@gmx.de>
 */
interface Parameter
{

    /**
     * @return string
     */
    public function getOperationName();

    /**
     * @param Operation $operation
     */
    public function initOperation(Operation $operation);
}