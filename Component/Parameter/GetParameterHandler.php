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
class GetParameterHandler //implements IParameterHandler
{

    public function initOperation(AOperation $operation, array $requestParameters)
    {
        $parameterMap = $operation->getGETParameterMap();

        $parameters = array();
        foreach ($parameterMap as $name) {
            $parameters[$name] = $this->getParameter($requestParameters, $name);
        }

        $operation->setParameters($parameters);
        return $operation;
    }


    /**
     * {@inheritdoc}
     */
    public function getParameter(array $requestParameters, $name, $caseSensitive = false, $xpath = null)
    {
        if ($name === null) {
            return null;
        }
        if ($caseSensitive) {
            return isset($requestParameters[$name]) ? $requestParameters[$name] : null;
        } else {
            foreach ($requestParameters as $key => $value) {
                if (strtoupper($name) === strtoupper($key)) {
                    return $value;
                }
            }

            return null;
        }
    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getOperation()
//    {
//        if ($this->operation === null) {
//            $this->setRequestParameters();
//            $request = $this->getParameter('request');
//
//            if ($request) {
//                $this->operation = $this->csw->operationForName($request);
//                if (!$this->operation->getHttpGet()) {
//                    throw new CswException('request', CswException::OperationNotSupported);
//                }
//                $parameterMap    = $this->operation->getGETParameterMap();
//
//                $parameters      = array();
//                foreach ($parameterMap as $name) {
//                    $parameters[$name] = $this->getParameter($name);
//                }
//
//                $this->operation->setParameters($parameters);
//            } else {
//                throw new CswException('request', CswException::MissingParameterValue);
//            }
//        }
//        return $this->operation;
//    }
//
//    /**
//     * Sets request parameters from request.
//     */
//    private function setRequestParameters()
//    {
//        $this->requestParameters = $this->csw->getRequestStack()->getCurrentRequest()->query->all();
//    }
}
