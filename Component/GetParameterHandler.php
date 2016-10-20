<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * {@inheritdoc}
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetParameterHandler extends AParameterHandler
{

    /**
     * The key value pair list of requested parameters
     * @var array $requestParameters
     */
    protected $requestParameters;

    /**
     * {@inheritdoc}
     */
    public function __construct(Csw $csw, $rootPrefix = 'csw', $rootUri = 'http://www.opengis.net/cat/csw/2.0.2')
    {
        parent::__construct($csw, $rootPrefix, $rootUri);
        $this->operation = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name = null, $xpath = null, $caseSensitive = false)
    {
        if($name === null && $xpath === null) {
            return null;
        }
        if (!$this->requestParameters) {
            $this->setRequestParameters();
        }
        if ($caseSensitive) {
            return isset($this->requestParameters[$name]) ? $this->requestParameters[$name] : null;
        } else {
            foreach ($this->requestParameters as $key => $value) {
                if (strtoupper($name) === strtoupper($key)) {
                    return $value;
                }
            }
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        if ($this->operation === null) {
            $this->setRequestParameters();
            $request          = $this->getParameter('request');
            if ($request === null) {
                throw new CswException('request', CswException::MISSINGPARAMETER);
            } else {
                $this->operation  = $this->csw->operationForName($request);
                $parameterMap       = $this->operation->getGETParameterMap();
                $parameters = array();
                foreach ($parameterMap as $name) {
                    $parameters[$name] = $this->getParameter($name);
                }
                $this->operation->setParameters($parameters);
            }
        }
        return $this->operation;
    }

    /**
     * Sets request parameters from request.
     */
    protected function setRequestParameters() {
        $this->requestParameters = $this->csw->getRequestStack()->getCurrentRequest()->query->all();
    }
}