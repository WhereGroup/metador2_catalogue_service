<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of GetCapabilities
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetCapabilities extends AOperation
{

    static $parameterMap = array(
        'version' => null,
        'service' => '/' . PostParameterHandler::DEFAULT_PREFIX . ':GetCapabilities/@service',
        'acceptVersion' => '/' . PostParameterHandler::DEFAULT_PREFIX . ':GetCapabilities/ows:AcceptVersions/ows:Version/text()',
        'outputFormat' => '/' . PostParameterHandler::DEFAULT_PREFIX . ':GetCapabilities/ows:AcceptFormats/ows:OutputFormat/text()',
    );

    protected $name = 'GetCapabilities';
    protected $sectionList;
    protected $operations;
    // Operation's parameters
    protected $postEncodingList;
    protected $postEncoding;
    protected $acceptVersion;
    protected $templates;

    public function __construct(Csw $csw, $configuration)
    {
        parent::__construct($csw, $configuration);
        $this->templates = $configuration['outpurFormats'];
        $this->sectionList = $this->csw->getSections();
        $this->postEncodingList = array_keys($configuration['postEncodings']);
        $this->postEncoding     = $this->postEncodingList[0]; # !!! IMPORTANT
        $this->operations = array();
        $operations = $this->csw->getOperations();
        foreach ($operations as $name => $value) {
            if($name !== $this->name) {
                $class = $value['class'];
                $this->operations[$name] = new $class($this->csw, $value);
            } else {
                $this->operations[$name] = $this;
            }
        }
        $this->sectionList = array();
        $sectionList = $this->csw->getSections();
        foreach ($sectionList as $name => $value) {
            $class = $value['class'];
            $this->sectionList[$name] = new $class($value);
        }
    }

    public function getSectionList()
    {
        return $this->sectionList;
    }
    
    public function getOperations()
    {
        return $this->operations;
    }


    public function getPostEncodingList()
    {
        return $this->postEncodingList;
    }

    public function getPostEncoding()
    {
        return $this->postEncoding;
    }

    public function getAcceptVersion()
    {
        return $this->acceptVersion;
    }

    public function setSectionList($sectionList)
    {
        $this->sectionList = $sectionList;
        return $this;
    }

    public function setPostEncodingList($postEncodingList)
    {
        $this->postEncodingList = $postEncodingList;
        return $this;
    }

    public function setPostEncoding($postEncoding)
    {
        if ($postEncoding && !in_array($postEncoding, $this->postEncodingList)) {
            $this->exceptions[] = new CswException('PostEncoding', CswException::InvalidParameterValue);
        } elseif ($postEncoding) {
            $this->postEncoding = $postEncoding;
        }
        return $this;
    }

    public function setAcceptVersion($acceptVersion)
    {
        if($acceptVersion && is_string($acceptVersion)) {
            $this->acceptVersion = preg_split('/\s?,\s?/', trim($acceptVersion));
        } elseif($acceptVersion && is_array($acceptVersion)) {
            $this->acceptVersion = $acceptVersion;
        }
        return $this;
    }


    public function getParameters()
    {
        return array(
            'sections' => array_keys($this->sectionList),
        );
    }

    public function getConstraints()
    {
        return array(
            'PostEncoding' => $this->postEncodingList
        );
    }

    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'PostEncoding':
                $this->setPostEncoding($value);
                break;
            case 'acceptVersion':
                $this->setAcceptVersion($value);
                break;
//            case 'ElementSetName':
//                $this->setElementSetName($value);
//                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    public function validateParameter()
    {
        if($this->acceptVersion && !in_array($this->version, $this->acceptVersion)){
            $this->exceptions[] = new CswException('acceptVersion', CswException::VersionNegotiationFailed);
        }
        parent::validateParameter();
    }

    public function createResult(AParameterHandler $handler)
    {
        if ($handler instanceof GetParameterHandler) {
            foreach (self::$parameterMap as $key => $value) {
                $this->setParameter($key, $handler->getParameter($key));
            }
        } elseif ($handler instanceof PostParameterHandler) {
            foreach (self::$parameterMap as $key => $value) {
                if($value === null) {
                    $this->setParameter($key, null);
                } else {
                    $this->setParameter($key, $handler->getParameter($value));
                }
            }
        }
        $this->validateParameter();
        return $this->csw->getTemplating()->render(
            $this->templates[$this->getOutputFormat()],
            array(
                'getcapabilities' => $this
            )
        );
    }
}