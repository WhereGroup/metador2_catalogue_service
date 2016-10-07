<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of GetHandler
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class PostParameterHandler extends AParameterHandler
{
    const DEFAULT_PREFIX = 'csw';
    protected $dom;
    protected $xpath;

    public function __construct($content)
    {
        $dom = new \DOMDocument();
        if (!@$dom->loadXML($content, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_XINCLUDE)) {
            throw new CswException('Can\'t parse a string to xml', CswException::ParsingError);
        }
        $this->dom = $dom;
        $this->xpath = new \DOMXPath($this->dom);
        foreach ($this->xpath->query('//namespace::*') as $node) {
            if (strlen($node->prefix)) {
                $this->xpath->registerNamespace($node->prefix, $node->namespaceURI);
            } else {
                $this->xpath->registerNamespace(self::DEFAULT_PREFIX, $node->namespaceURI); #@TODO change register namespace
            }
        }
    }

    public function getParameter($xpath, $caseSensitive = true)
    {
        $result = $this->getValue($xpath);
        return $result;
    }

    public function getOperation()
    {
        return $this->dom->documentElement->localName;
    }

    /**
     *
     * @param string $xpath xpath expression
     * @param \DOMElement $contextElm the node to use as context for evaluating the XPath expression.
     * @return mixed
     */
    private function getValue($xpath, $contextElm = null)
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

    private function getNodeValue(\DOMNode $node)
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