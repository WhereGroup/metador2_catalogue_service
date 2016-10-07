<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of GetHandler
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetParameterHandler extends AParameterHandler
{
    protected $parameters;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameter($name, $caseSensitive = false)
    {
        if($caseSensitive){
            return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
        } else {
            foreach ($this->parameters as $key => $value) {
                if(strtoupper($name) === strtoupper($key)){
                    return $value;
                }
            }
        }
    }

    public function getOperation()
    {
        if (!($operation = $this->getParameter('request'))) {
            throw new CswException('request', CswException::MISSINGPARAMETER);
        }
        return $operation;
    }
}