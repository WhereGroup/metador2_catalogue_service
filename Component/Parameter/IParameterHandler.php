<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 16.08.17
 * Time: 15:15
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation;

/**
 * Interface IParameterHandler
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter
 * @author Paul Schmidt <panadium@gmx.de>
 */
interface IParameterHandler
{

    /**
     * @return string
     */
    public function getOperationName();

    /**
     * @param AOperation $operation
     */
    public function initOperation(AOperation $operation);
}