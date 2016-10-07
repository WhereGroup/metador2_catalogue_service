<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of Operation
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
abstract class AOperation
{
    protected $csw;
    protected $httpGet;
    protected $httpPost;
    protected $service;
    protected $version;
    protected $name;
    protected $outputFormatList;
    protected $outputFormat;

    protected $exceptions;

    public function __construct(Csw $csw, $configuration)
    {
        $this->csw              = $csw;
        $this->httpGet          = $configuration['http']['get'] ? $this->csw->getHttpGet() : null;
        $this->httpPost         = $configuration['http']['post'] ? $this->csw->getHttpPost() : null;
        $this->outputFormatList = array_keys($configuration['outpurFormats']);
        $this->outputFormat     = $this->outputFormatList[0]; # !!! IMPORTANT
        $this->exceptions = array();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getHttpGet()
    {
        return $this->httpGet;
    }

    public function getHttpPost()
    {
        return $this->httpPost;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getOutputFormatList()
    {
        return $this->outputFormatList;
    }

    public function getOutputFormat()
    {
        return $this->outputFormat;
    }

    public function setHttpGet($httpGet)
    {
        $this->httpGet = $httpGet;
        return $this;
    }

    public function setHttpPost($httpPost)
    {
        $this->httpPost = $httpPost;
        return $this;
    }

    public function setService($service)
    {
        if ($service === Csw::SERVICE) {
            $this->service = $service;
        } elseif ($service === null || $service === '') {
            $this->exceptions[] = new CswException('service', CswException::InvalidParameterValue);
        } else {
            $this->exceptions[] = new CswException('service', CswException::MissingParameterValue);
        }
    }

    public function setVersion($version)
    {
        if ($version && in_array($version, Csw::VERSIONLIST)) {
            $this->version = $version;
        } else {
            $this->version = Csw::VERSION;
        }
    }

    public function setOutputFormatList($outputFormatList)
    {
        $this->outputFormatList = $outputFormatList;
        return $this;
    }

    public function setOutputFormat($outputFormat)
    {
        if ($outputFormat && !in_array($outputFormat, $this->outputFormatList)) {
            $this->exceptions[] = new CswException('outputFormat', CswException::InvalidParameterValue);
        } elseif ($outputFormat) {
            $this->outputFormat = $outputFormat;
        }
        return $this;
    }

    public function getParameters()
    {
        return array();
    }

    public function getConstraints()
    {
        return array();
    }

    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'version':
                $this->setVersion($value);
                break;
            case 'service':
                $this->setService($value);
                break;
            case 'outputFormat':
                $this->setOutputFormat($value);
                break;
            case 'request':
                break;
            default:
                throw new \Exception('!!!!!not defined parameter:' . $name);
        }
    }

    public function validateParameter()
    {
        if(count($this->exceptions)=== 0) {
            return true;
        } else {
            $exception = null;
            foreach ($this->exceptions as $exc) {
                if($exception) {
                    $exception = new CswException($exc->getMessage(), $exc->getCode(), $exception->getPrevious());
                } else {
                    $exception = new CswException($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
                }
            }
            throw $exception;
        }
    }

//    # @TODO check all parameters
//    abstract public function validateParameter();

    abstract public function createResult(AParameterHandler $handler);
}