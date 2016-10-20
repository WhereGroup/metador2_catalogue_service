<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Filter\AOperation;
//use Plugins\WhereGroup\CatalogueServiceBundle\Component\AParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw;

/**
 * {@inheritdoc}
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class PostSaxParameterHandler extends AParameterHandler
{
    protected $csw;
    protected $saxParser;
    protected $inited = false;
    protected $namespaces;
    protected $defUri;
    protected $defPrefix;
    protected $xpathStr;
    protected $map;
    protected $operation;
    protected $parser;
    protected $eventHandler;
//
//    protected $eventHandlers = array(
//
//    );

    /**
     * The parameter map for operation
     * @var array $parameterMap
     */
    protected $parameterMap;

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
        $this->namespaces = array();
        $this->inited     = false;
        $this->xpathStr   = '';
        $this->parser     = null;
        $this->eventHandler = new SaxNodeEventHandler($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name = null, $xpath = null, $caseSensitive = false)
    {
        if ($this->parser === null) {
            $this->parse();
        }
        throw new \Exception('SAX Post getParameter is not yet implemented.');
    }

    protected function initOperation(array $prefexedName, $attributes)
    {
        $this->namespaces = array();
        foreach ($attributes as $key => $value) {
            if (strpos($key, 'xmlns') === 0) {
                $help = explode(':', $key);
                if (count($help) === 1) {
                    $this->defUri    = $value;
                    $this->defPrefix = $this->defUri === $this->rootUri ? $this->rootPrefix : self::EXTERNAL_PREFIX;
                } else {
                    $this->namespaces[$help[1]] = $value;
                }
            }
        }
        $this->operation         = $this->csw->operationForName($prefexedName[1]);
        $this->parameterMap      = $this->operation->getPOSTParameterMap();
        $this->requestParameters = array();
        $this->inited            = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        if ($this->parser === null) {
            $this->parse();
        }
        $this->operation->setParameters($this->requestParameters);
        return $this->operation;
    }

    protected function parse()
    {
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($this->parser, "startElement", "endElement");
        xml_set_character_data_handler($this->parser, "elementContent");
        // @TODO read fram an input stream
        xml_parse($this->parser, $this->csw->getRequestStack()->getCurrentRequest()->getContent());
    }

    public function getPrefixedName($QName)
    {
        $help = explode(':', $QName);
        if (count($help) === 1) {
            return array($this->defPrefix, $help[0]);
        } else {
            return $help;
        }
    }

    protected function createElementNs(array $prefexedName)
    {
        return '/' . $prefexedName[0] . ':' . $prefexedName[1];
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

    public function getParameterMap()
    {
        return $this->parameterMap;
    }

    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * Callback for the start of each element
     * @param type $parser
     * @param string $elementName element name
     * @param array $attributes attributes
     */
    protected function startElement($parser, $elementName, $attributes)
    {
        if (!$this->inited) { // root element
            $this->initOperation($this->getPrefixedName($elementName), $attributes);
        }
        $this->eventHandler->onElementStart($elementName, $attributes);
    }

    /**
     * Callback for the end of each element
     * @param type $parser parser
     * @param string $elementName element name
     */
    protected function endElement($parser, $elementName)
    {
        $this->eventHandler->onElementEnd($elementName);
    }

    /**
     * Callback for the content within an element.
     * @param type $parser
     * @param string $content content
     */
    protected function elementContent($parser, $content)
    {
        $this->eventHandler->onElementContent($content);
    }

    public function setRequestParameterValue($xpathStr, $value)
    {
        if (isset($this->parameterMap[$xpathStr])) {
            if (!isset($this->requestParameters[$this->parameterMap[$xpathStr]])) {
                $this->requestParameters[$this->parameterMap[$xpathStr]] = $value;
            } elseif (is_string($this->requestParameters[$this->parameterMap[$xpathStr]])) {
                $this->requestParameters[$this->parameterMap[$xpathStr]] = array($this->requestParameters[$this->parameterMap[$xpathStr]], $value);
            } elseif (is_array($this->requestParameters[$this->parameterMap[$xpathStr]])) {
                $this->requestParameters[$this->parameterMap[$xpathStr]][] = $value;
            }
        }
    }
}