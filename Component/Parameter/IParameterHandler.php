<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw;

/**
 * The classe AParameterHandler handles the requested parameters
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
interface IParameterHandler
{
//    const EXTERNAL_PREFIX = 'my_prefix';
//    protected $csw;
//    protected $rootPrefix;
//    protected $rootUri;
//    protected $operation;
    
    public static function create(Csw $csw, $rootPrefix = 'csw', $rootUri = 'http://www.opengis.net/cat/csw/2.0.2');
//    {
//        $this->csw = $csw;
//        $this->rootPrefix = $rootPrefix;
//        $this->rootUri    = $rootUri;
//    }

    /**
     * Finds and returns the the parameter's value
     * @param string $name a parameter name
     * @param string $xpath xpah for a parameter
     * @param boolean $caseSensitive enables case sentitive finding of a parameter.
     * @return mixed parameter value
     */
    public function getParameter($name = null, $xpath = null, $caseSensitive = false);

    /**
     * Identifies the operation on the basis of given parameters.
     * @return AOperetion operation
     */
    public function getOperation();
}