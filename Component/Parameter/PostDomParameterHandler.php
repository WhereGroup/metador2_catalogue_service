<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * Description of GetHandler
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class PostDomParameterHandler implements IParameterHandler
{
    const EXTERNAL_PREFIX = 'my_prefix';
    
    protected $csw;
    protected $rootPrefix;
    protected $rootUri;
    protected $operation;
    
    /**
     * The requested xml
     * @var \DOMDocument $dom
     */
    protected $dom;

    /**
     * The xpath for a requested xml
     * @var \DOMXPath $xpath
     */
    protected $xpath;


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
    public function getParameter($name = null, $xpath = null, $caseSensitive = true)
    {
        if (!$this->dom || ($name === null && $xpath === null)) {
            return null;
        }
        $result = $this->getValue($xpath);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation()
    {
        if($this->operation === null) {
            $dom = new \DOMDocument();
            $content = $this->csw->getRequestStack()->getCurrentRequest()->getContent();
            if (!@$dom->loadXML($content, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_XINCLUDE)) {
                throw new CswException('Can\'t parse a string to xml', CswException::ParsingError);
            }
            $this->dom = $dom;
            $this->xpath = new \DOMXPath($this->dom);
            foreach ($this->xpath->query('//namespace::*') as $node) {
                if (strlen($node->prefix)) {
                    $this->xpath->registerNamespace($node->prefix, $node->namespaceURI);
                } else {
                    if ($node->namespaceURI === $this->rootUri) {
                        $this->xpath->registerNamespace($this->rootPrefix, $node->namespaceURI);
                    } else {
                        $this->xpath->registerNamespace(self::EXTERNAL_PREFIX, $node->namespaceURI);
                    }
                }
            }
            $this->operation = $this->csw->operationForName($this->dom->documentElement->localName);
            if (!$this->operation->getHttpPost()) {
                throw new CswException('request', CswException::OperationNotSupported);
            }
            $parameterMap       = $this->operation->getPOSTParameterMap();
            $parameters = array();
            foreach ($parameterMap as $key => $value) {
                $parameters[$value] = $this->getParameter(null, $key);
            }
            $this->operation->setParameters($parameters);
        }
        return $this->operation;
    }

    /**
     * Returns value for a given xpath
     * @param string $xpath xpath expression
     * @param \DOMElement $contextElm the node to use as context for evaluating the XPath expression.
     * @return mixed
     */
    protected function getValue($xpath, $contextElm = null)
    {
        // DOMNodeList
        $list = $this->xpath->query($xpath, $contextElm ? $contextElm : $this->dom->documentElement);
        if ($list->length === 0) {
            return null;
        } elseif ($list->length === 1) {
            return $this->getNodeValue($list->item(0));
        } else {
            $result = array();
            foreach ($list as $item) {
                $result[] = $this->getNodeValue($item);
            }
            return $result;
        }
    }

    /**
     * Returns the node value
     * @param \DOMNode $node
     * @return mixed node value
     */
    protected function getNodeValue(\DOMNode $node)
    {
        try {
            if (!$node) {
                return null;
            } elseif ($node->nodeType == XML_ATTRIBUTE_NODE) {
                return $node->value;
            } else if ($node->nodeType == XML_TEXT_NODE) {
                return $node->wholeText;
            } else if ($node->nodeType == XML_ELEMENT_NODE) {
                return $node;
            } else if ($node->nodeType == XML_CDATA_SECTION_NODE) {
                return $node->wholeText;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}