<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * {@inheritdoc}
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetParameterHandler
{
    /**
     * @var array
     */
    private $requestParameters;

    public function __construct(array $requestParameters)
    {
        $this->requestParameters = $requestParameters;
    }

    /**
     * @return string
     */
    public function getOperationName()
    {
        return $this->getParameter('request');
    }

    /**
     * @param AOperation $operation
     * @return AOperation
     */
    public function initOperation(AOperation $operation)
    {
        $parameterMap = $operation->getGETParameterMap();

        $parameters = array();
        foreach ($parameterMap as $name) {
            $parameters[$name] = $this->getParameter($name);
        }

        $operation->setParameters($parameters);

        return $operation;
    }


    /**
     * @param $name
     * @param bool $caseSensitive
     * @param null $xpath
     * @return mixed|null
     */
    private function getParameter($name, $caseSensitive = false, $xpath = null)
    {
        if ($name === null) {
            return null;
        }
        if ($caseSensitive) {
            return isset($requestParameters[$name]) ? $this->requestParameters[$name] : null;
        } else {
            foreach ($this->requestParameters as $key => $value) {
                if (strtoupper($name) === strtoupper($key)) {
                    return $value;
                }
            }

            return null;
        }
    }
}
