<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of Operation
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetRecordById extends AOperation
{
    static $parameterMap = array(
        'version' => '/child::*/@',
        'service' => '/child::*/@',
        'outputSchema' => '/child::*/@',
        'outputFormat' => '/child::*/@',
        'ElementSetName' => '/child::*/@',
        'PostEncoding' => '/child::*/@',
    );
    protected $elementSetNameList = array('brief', 'summary', 'full');
    protected $elementSetName     = 'summary'; // default value, parameter optional
    protected $outputSchemaList = array(
        'http://www.opengis.net/cat/csw/2.0.2',
        'http://www.isotc211.org/2005/gmd'
    );
    protected $outputSchema; // default value is a first position at the list $outputSchemaList
    protected $postEncodingList = array('XML');
    protected $postEncoding;

    public function __construct(Csw $csw = null, $httpGet = null, $httpPost = null)
    {
        parent::__construct($csw, $httpGet, null);#$httpPost);
        $this->name           = 'GetRecordById';
        $this->elementSetName = 'summary'; # standard value, optional
        $this->outputSchema   = $this->outputSchemaList[0];
    }

    public function getElementSetNameList()
    {
        return $this->elementSetNameList;
    }

    public function getElementSetName()
    {
        return $this->elementSetName;
    }

    public function getOutputFormatList()
    {
        return $this->outputFormatList;
    }

    public function getOutputFormat()
    {
        return $this->outputFormat;
    }

    public function getOutputSchemaList()
    {
        return $this->outputSchemaList;
    }

    public function getOutputSchema()
    {
        return $this->outputSchema;
    }

    public function getPostEncodingList()
    {
        return $this->postEncodingList;
    }

    public function getPostEncoding()
    {
        return $this->postEncoding;
    }

    public function setElementSetNameList($elementSetNameList)
    {
        $this->elementSetNameList = $elementSetNameList;
        return $this;
    }

    public function setElementSetName($elementSetName)
    {
        if ($elementSetName && !in_array($elementSetName, $this->elementSetNameList)) {
            throw new CswException('ElementSetName', CswException::INVALIDPARAMETERVALUE);
        } elseif ($elementSetName) {
            $this->elementSetName = $elementSetName;
        }
        return $this;
    }

    public function setOutputSchemaList($outputSchemaList)
    {
        $this->outputSchemaList = $outputSchemaList;
        return $this;
    }

    public function setOutputSchema($outputSchema)
    {
        if ($outputSchema && !in_array($outputSchema, $this->outputSchemaList)) {
            throw new CswException('outputSchema', CswException::INVALIDPARAMETERVALUE);
        } elseif ($outputSchema) {
            $this->outputSchema = $outputSchema;
        }
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
            throw new CswException('PostEncoding', CswException::INVALIDPARAMETERVALUE);
        } elseif ($postEncoding) {
            $this->postEncoding = $postEncoding;
        }
        return $this;
    }

    public function getParameters()
    {
        return array(
            'outputSchema' => $this->outputSchemaList,
            'outputFormat' => $this->outputFormatList,
            'ElementSetName' => $this->elementSetNameList
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
        // @TODO with reflection ???

        switch ($name) {
            case 'outputSchema':
                $this->setOutputSchema($value);
                break;
            case 'PostEncoding':
                $this->setPostEncoding($value);
                break;
            case 'ElementSetName':
                $this->setElementSetName($value);
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    public function createResult(AParameterHandler $handler)
    {
        if ($handler instanceof GetParameterHandler) {
            foreach (self::$parameterMap as $key => $value) {
                $this->setParameter($key, $handler->getParameter($key));
            }
        } elseif ($handler instanceof PostParameterHandler) {
            foreach (self::$parameterMap as $key => $value) {
                $this->setParameter($key, $handler->getParameter($value));
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