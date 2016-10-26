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
class PostSaxParameterHandler extends SimpleSaxHandler implements IParameterHandler, ISaxHandler
{
    protected $csw;
    protected $rootPrefix;
    protected $rootUri;
    protected $operation;
    protected $namespaces;
    protected $defUri;
    protected $defPrefix;


    /**
     * Creates an instance.
     * @param Csw $csw
     * @param string $rootPrefix
     * @param string $rootUri
     */
    public function __construct(Csw $csw, $rootPrefix = 'csw', $rootUri = 'http://www.opengis.net/cat/csw/2.0.2')
    {
        $this->csw = $csw;
        $this->rootPrefix = $rootPrefix;
        $this->rootUri    = $rootUri;
        $this->namespaces = array();
        $this->inited     = false;
        $this->xpathStr   = '';
        $this->saxParser     = null;
//        $this->eventHandler = new SaxNodeEventHandler($this);
    }

    /**
     * {@inheritdoc}
     */
    public static function create(Csw $csw, $rootPrefix = 'csw', $rootUri = 'http://www.opengis.net/cat/csw/2.0.2')
    {
        return new self($csw, $rootPrefix, $rootUri);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name = null, $xpath = null, $caseSensitive = false)
    {
        if ($this->saxParser === null) {
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
        $this->parameters = array();
        $this->inited            = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefixedName($QName)
    {
        $help = explode(':', $QName);
        if (count($help) === 1) {
            return array($this->defPrefix, $help[0]);
        } else {
            return $help;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startElement($parser, $elementName, $attributes)
    {
        if (!$this->inited) { // root element
            $this->eventHandler = new SaxNodeEventHandler($this);
            $this->initOperation($this->getPrefixedName($elementName), $attributes);
        }
        $this->eventHandler->onElementStart($elementName, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        if ($this->saxParser === null) {
            $this->saxParser = xml_parser_create();
            xml_set_object($this->saxParser, $this);
            xml_parser_set_option($this->saxParser, XML_OPTION_CASE_FOLDING, false);
            xml_set_element_handler($this->saxParser, "startElement", "endElement");
            xml_set_character_data_handler($this->saxParser, "elementContent");
            // @TODO read fram an input stream
            xml_parse($this->saxParser, $this->csw->getRequestStack()->getCurrentRequest()->getContent());
        }
        $this->operation->setParameters($this->parameters);
        return $this->operation;
    }
}