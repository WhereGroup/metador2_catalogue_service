<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Operation;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

/**
 * Class PostDomParameter
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter
 * @author Paul Schmidt <panadium@gmx.de>
 */
class PostDomParameter implements Parameter
{
    const EXTERNAL_PREFIX = 'my_prefix';

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
     * @var string
     */
    protected $rootPrefix;

    /**
     * @var string
     */
    protected $rootUri;

    /**
     * PostDomParameter constructor.
     * @param $content
     * @param string $rootPrefix
     * @param string $rootUri
     * @throws CswException
     */
    public function __construct($content, $rootPrefix = 'csw', $rootUri = 'http://www.opengis.net/cat/csw/2.0.2')
    {
        $this->rootPrefix = $rootPrefix;
        $this->rootUri = $rootUri;

        $dom = new \DOMDocument();
        if (!@$dom->loadXML($content, LIBXML_DTDLOAD | LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_XINCLUDE)) {
            throw new CswException('Can\'t parse a string to xml', CswException::OperationParsingFailed);
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
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationName()
    {
        return $this->dom->documentElement->localName;
    }

    /**
     * {@inheritdoc}
     */
    public function initOperation(Operation $operation)
    {
        $parameterMap = $operation->getPOSTParameterMap();
        $parameters = array();
        foreach ($parameterMap as $key => $value) {
            $parameters[$value] = $this->getParameter($key);
        }
        $operation->setParameters($parameters);
        return $operation;
    }

    /**
     * @param string $xpath
     * @return mixed|null
     */
    protected function getParameter($xpath, $contextElm = null, $allowSingle = true)
    {
        if (!$this->dom) {
            return null;
        }

        return $this->getValue($xpath, $contextElm, $allowSingle);
    }

    /**
     * Returns value for a given xpath
     * @param string $xpath xpath expression
     * @param \DOMElement $contextElm the node to use as context for evaluating the XPath expression.
     * @return mixed
     */
    protected function getValue($xpath, $contextElm = null, $allowSingle = true)
    {
        // DOMNodeList
        $list = $this->xpath->query($xpath, $contextElm ? $contextElm : $this->dom->documentElement);
        if ($list->length === 0) {
            return null;
        } elseif ($list->length === 1 && $allowSingle) {
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
            } else {
                if ($node->nodeType == XML_TEXT_NODE) {
                    return $node->wholeText;
                } else {
                    if ($node->nodeType == XML_ELEMENT_NODE) {
                        return $node;
                    } else {
                        if ($node->nodeType == XML_CDATA_SECTION_NODE) {
                            return $node->wholeText;
                        } else {
                            return null;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}