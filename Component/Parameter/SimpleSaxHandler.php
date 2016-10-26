<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

/**
 * Description of StringXmlParser
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class SimpleSaxHandler implements ISaxHandler
{

    protected $inited = false;
    protected $saxParser;
    protected $eventHandler;
    protected $xpathStr;
    
    /**
     * The parameter map for operation
     * @var array $parameterMap
     */
    protected $parameterMap;

    /**
     * The list of parameters to be find at a xml.
     * @var array $parameters
     */
    protected $parameters;

    private function __construct()
    {
        $this->parameterMap      = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefixedName($QName)
    {
        $help = explode(':', $QName);
        if (count($help) === 1) {
            return array(null, $help[0]);
        } else {
            return $help;
        }
    }

    public function xpathFromRoot(array $prefexedName)
    {
        $this->xpathStr .= $this->createElementNs($prefexedName);
    }

    public function xpathToRoot(array $prefexedName)
    {
        $str            = $this->createElementNs($prefexedName);
        $this->xpathStr = substr($this->xpathStr, 0, strlen($this->xpathStr) - strlen($str));
    }

    public function getXpathStr()
    {
        return $this->xpathStr;
    }

    protected function createElementNs(array $prefexedName)
    {
        if(count($prefexedName) === 2 && $prefexedName[0] !== null) {
            return '/' . $prefexedName[0] . ':' . $prefexedName[1];
        } else {
            return '/' . $prefexedName[1];
        }
    }

    public function getParameterMap()
    {
        return $this->parameterMap;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameter($xpathStr, $value)
    {
        if (isset($this->parameterMap[$xpathStr])) {
            if (!isset($this->parameters[$this->parameterMap[$xpathStr]])) {
                $this->parameters[$this->parameterMap[$xpathStr]] = $value;
            } elseif (is_string($this->parameters[$this->parameterMap[$xpathStr]])) {
                $this->parameters[$this->parameterMap[$xpathStr]] = array($this->parameters[$this->parameterMap[$xpathStr]], $value);
            } elseif (is_array($this->parameters[$this->parameterMap[$xpathStr]])) {
                $this->parameters[$this->parameterMap[$xpathStr]][] = $value;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startElement($parser, $elementName, $attributes)
    {
        if (!$this->inited) { // root element
            $this->eventHandler = new SaxNodeEventHandler($this);
            if (!$this->parameterMap) {
                $this->parameterMap = array('/'.$elementName);
            }
            $this->inited = true;
        }
        $this->eventHandler->onElementStart($elementName, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function endElement($parser, $elementName)
    {
        $this->eventHandler->onElementEnd($elementName);
    }

    /**
     * {@inheritdoc}
     */
    public function elementContent($parser, $content)
    {
        $this->eventHandler->onElementContent($content);
    }

    public final static function toArray($xmsString, array $parameterMap)
    {
        $self = new self();
        $self->saxParser = xml_parser_create();
        xml_set_object($self->saxParser, $self);
        xml_parser_set_option($self->saxParser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($self->saxParser, "startElement", "endElement");
        xml_set_character_data_handler($self->saxParser, "elementContent");
        // @TODO read fram an input stream
        $self->parameterMap = $parameterMap;
        xml_parse($self->saxParser, $xmsString);
        return $self->parameters;
    }
}