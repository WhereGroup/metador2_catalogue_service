<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Search;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use WhereGroup\CoreBundle\Component\Search\Expression;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;
use WhereGroup\CoreBundle\Component\Search\FilterReader;
use WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException;

/**
 * Class GmlFilterReader
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component\Search
 * @author Paul Schmidt <panadium@gmx.de>
 */
class GmlFilterReader implements FilterReader
{
    /**
     * @var ExprHandler
     */
    protected $exprHandler;

    /**
     * JsonFilterReader constructor.
     * @param ExprHandler $exprHandler
     */
    private function __construct(ExprHandler $exprHandler)
    {
        $this->exprHandler = $exprHandler;
    }

    /**
     * @param $name
     * @return string
     */
    private function getName($name)
    {
        switch (strtolower($name)) {
            case 'title':
                return 'title';
            case 'subject':
                return 'keywords';
            case 'anytext':
                return 'anyText';
            case 'identifier':
            case 'fileidentifier':
                return 'id';
            case 'hierarchylevel':
                return 'hierarchyLevel';
            case 'topiccategory':
                return 'topicCategory';
            case 'insertuser':
                return 'insertUser';
            case 'insertusername':
                return 'insertUsername';
        }

        return $name;
    }

    /**
     * @param mixed $filter
     * @param ExprHandler $expression
     * @return null|Expression
     * @throws CswException
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public static function readFromCsw($filter, ExprHandler $expression)
    {
        $parameters = [];
        $reader = new GmlFilterReader($expression);
        $expression = $reader->getExpression($filter, $expression, $parameters);

        if (is_array($expression) && count($expression) !== 1) {
            return null;
        }

        return new Expression($expression[0], $parameters);
    }

    /**
     * @param mixed $filter
     * @param ExprHandler $expression
     * @return null|Expression
     * @throws CswException
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     */
    public static function read($filter, ExprHandler $expression)
    {
        $parameters = [];
        $reader = new GmlFilterReader($expression);
        $expression = $reader->getExpression($filter, $expression, $parameters);

        if (is_array($expression) && count($expression) !== 1) {
            return null;
        }

        return new Expression($expression[0], $parameters);
    }

    /**
     * @param \DOMElement $filter
     * @param ExprHandler $exprH
     * @param             $parameters
     * @return array|mixed|null
     * @throws \WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException
     * @throws CswException
     */
    private function getExpression(\DOMElement $filter, ExprHandler $exprH, &$parameters)
    {
        $items = [];
        /* @var \DOMElement $child */
        $child = null;
        foreach ($filter->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            switch ($child->localName) {
                case 'And':
                    $list = $this->getExpression($child, $exprH, $parameters);
                    if (count($list) > 1) {
                        $items[] = $exprH->andx($list);
                    } elseif (count($list) === 1) {
                        $items[] = $list[0];
                    }
                    break;
                case 'Or':
                    $list = $this->getExpression($child, $exprH, $parameters);
                    if (count($list) > 1) {
                        $items[] = $exprH->orx($list);
                    } elseif (count($list) === 1) {
                        $items[] = $list[0];
                    }
                    break;
                case 'Not':
                    $item = $this->getExpression($child, $exprH, $parameters);
                    $items[] = $exprH->not($item);
                    break;
                case 'PropertyIsEqualTo':
                    $operands = self::getComparisonContent($child);

                    // TODO: CLEAN UP !!! :'(
                    if (isset($operands['name']) && strtolower($operands['name']) === 'subject') {
                        $items[] = $exprH->like(
                            $this->getName($operands['name']),
                            "%".$operands['literal']."%",
                            $parameters,
                            "\\",
                            "_",
                            "%"
                        );
                        break;
                    }

                    $items[] = $exprH->eq($this->getName($operands['name']), $operands['literal'], $parameters);
                    break;
                case 'PropertyIsNotEqualTo':
                    $operands = self::getComparisonContent($child);
                    $items[] = $exprH->neq($this->getName($operands['name']), $operands['literal'], $parameters);
                    break;
                case 'PropertyIsLike':
                    $operands = self::getComparisonContent($child);
                    $escapeChar = $child->getAttribute('escapeChar');
                    if (!$escapeChar) {
                        throw new CswException('escapeChar', CswException::MISSINGPARAMETERVALUE);
                    } elseif (strlen($escapeChar) !== 1) {
                        throw new CswException('escapeChar', CswException::INVALIDPARAMETERVALUE);
                    }
                    $singleChar = $child->getAttribute('singleChar');
                    if (!$singleChar) {
                        throw new CswException('singleChar', CswException::MISSINGPARAMETERVALUE);
                    } elseif (strlen($singleChar) !== 1) {
                        throw new CswException('singleChar', CswException::INVALIDPARAMETERVALUE);
                    }
                    $wildCard = $child->getAttribute('wildCard');
                    if (!$wildCard) {
                        throw new CswException('wildCard', CswException::MISSINGPARAMETERVALUE);
                    } elseif (strlen($wildCard) !== 1) {
                        throw new CswException('wildCard', CswException::INVALIDPARAMETERVALUE);
                    }
                    $items[] = $exprH->like(
                        $this->getName($operands['name']),
                        $operands['literal'],
                        $parameters,
                        $escapeChar,
                        $singleChar,
                        $wildCard
                    );
                    break;
                case 'PropertyIsBetween':
                    $operands = self::getBetweenContent($child);
                    $items[] = $exprH->between(
                        $this->getName($operands['name']),
                        $operands['lower'],
                        $operands['upper'],
                        $parameters
                    );
                    break;
                case 'PropertyIsGreaterThan':
                    $operands = self::getGtLtContent($child);
                    $items[] = $exprH->gt($this->getName($operands['name']), $operands['literal'], $parameters);
                    break;
                case 'PropertyIsGreaterThanOrEqualTo':
                    $operands = self::getGtLtContent($child);
                    $items[] = $exprH->gte($this->getName($operands['name']), $operands['literal'], $parameters);
                    break;
                case 'PropertyIsLessThan':
                    $operands = self::getGtLtContent($child);
                    $items[] = $exprH->lt($this->getName($operands['name']), $operands['literal'], $parameters);
                    break;
                case 'PropertyIsLessThanOrEqualTo':
                    $operands = self::getGtLtContent($child);
                    $items[] = $exprH->lte($this->getName($operands['name']), $operands['literal'], $parameters);
                    break;
                case 'PropertyIsNull':
                    $operands = self::getComparisonContent($child);
                    $items[] = $exprH->isNull($this->getName($operands['name']));
                    break;
                case 'BBOX':
                case 'Intersects':
                    $operands = self::getSpatialContent($child);
                    $items[] = $exprH->bbox($this->getName($operands['name']), $operands['geom'], $parameters);
                    break;
                case 'Contains':
                    $operands = self::getSpatialContent($child);
                    $items[] = $exprH->contains($this->getName($operands['name']), $operands['geom'], $parameters);
                    break;
                case 'Within':
                    $operands = self::getSpatialContent($child);
                    $items[] = $exprH->within($this->getName($operands['name']), $operands['geom'], $parameters);
                    break;
                default:
                    throw new PropertyNameNotFoundException($child->localName);
            }
        }

        return $items;
    }

    /**
     * @param \DOMElement $operator
     * @return array
     */
    private static function getComparisonContent(\DOMElement $operator)
    {
        $operands = [];
        /* @var \DOMNode $child */
        $child = null;
        foreach ($operator->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            switch ($child->localName) {
                case 'PropertyName':
                    $operands['name'] = $child->textContent;
                    break;
                case 'Literal':
                    $operands['literal'] = $child->textContent;
                    break;
                default:
                    null;
            }
        }

        return $operands;
    }

    /**
     * @param \DOMElement $operator
     * @return array|string
     */
    private static function getBetweenContent(\DOMElement $operator)
    {
        $operands = [];
        /* @var \DOMNode $child */
        $child = null;
        foreach ($operator->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            switch ($child->localName) {
                case 'ValueReference':
                    $operands['name'] = $child->textContent;
                    break;
                case 'LowerBoundary':
                    $operands['lower'] = self::getBetweenContent($child);
                    break;
                case 'UpperBoundary':
                    $operands['upper'] = self::getBetweenContent($child);
                    break;
                case 'Literal':
                    return $child->textContent;
                default:
                    null;
            }
        }

        return $operands;
    }

    /**
     * @param \DOMElement $operator
     * @return array
     */
    private static function getGtLtContent(\DOMElement $operator)
    {
        $operands = [];
        /* @var \DOMNode $child */
        $child = null;
        foreach ($operator->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            switch ($child->localName) {
                case 'ValueReference':
                    $operands['name'] = $child->textContent;
                    break;
                case 'Literal':
                    $operands['literal'] = $child->textContent;
                    break;
                default:
                    null;
            }
        }

        return $operands;
    }

    /**
     * @param \DOMElement $operator
     * @return array
     */
    private static function getSpatialContent(\DOMElement $operator)
    {
        $operands = [];
        /* @var \DOMNode $child */
        $child = null;
        foreach ($operator->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            switch ($child->localName) {
                case 'PropertyName':
                    $operands['name'] = $child->textContent;
                    break;
                case 'Envelope':
                    $help = self::getSpatialContent($child);
                    $bbox = array_merge($help[0], $help[1]);
                    $ords = self::toPolygonOrdinates($bbox);
                    $jsonCoords = self::toJsonCoordinates($ords);
                    $operands['geom'] = self::createGeoJson(
                        'Polygon',
                        array($jsonCoords),
                        $child->getAttribute('srsName'),
                        $bbox
                    );
                    break;
                case 'lowerCorner':
                    $operands[0] = self::splitStringOrdinates($child->textContent);
                    break;
                case 'upperCorner':
                    $operands[1] = self::splitStringOrdinates($child->textContent);
                    break;
                case 'Point':
                    $coord = self::getSpatialContent($child);
                    $operands['geom'] = self::createGeoJson('Point', $coord[0], $child->getAttribute('srsName'));
                    break;
                case 'pos':
                    $operands[] = self::splitStringOrdinates($child->textContent);
                    break;
                case 'Polygon':
                    $help = self::getSpatialContent($child);
                    $coords = [];
                    foreach ($help as $ring) {
                        $coords[] = self::toJsonCoordinates($ring);
                    }
                    $operands['geom'] = self::createGeoJson(
                        'Polygon',
                        $coords,
                        $child->getAttribute('srsName')
                    );
                    break;
                case 'exterior':
                    $help = self::getSpatialContent($child);
                    $operands[] = $help[0];
                    break;
                case 'interior':
                    $help = self::getSpatialContent($child);
                    $operands[] = $help[0];
                    break;
                case 'LinearRing':
                    $help = self::getSpatialContent($child);
                    $operands[] = $help[0];
                    break;
                case 'posList':
                    $operands[] = self::splitStringOrdinates($child->textContent);
                    break;
                default:
                    null;
            }
        }

        return $operands;
    }

    /**
     * @param string $ordinatesString
     * @return array
     */
    private static function splitStringOrdinates($ordinatesString)
    {
        $stringOrdinates = preg_split('/[\s,]/', $ordinatesString);

        return array_map(
            function ($cont) {
                return floatval($cont);
            },
            $stringOrdinates
        );
    }

    /**
     * @param $type
     * @param array $coordinates
     * @param array|null $bbox
     * @return array
     */
    private static function createGeoJson($type, array $coordinates, $crs = null, array $bbox = null)
    {
        $geom = array(
            "type" => $type,
            "coordinates" => $coordinates,
        );

        if ($crs !== null) {
            $geom['crs'] = array(
                "type" => "name",
                "properties" => array(
                    "name" => $crs,
                ),
            );
        }
        if ($bbox !== null) {
            return array(
                "type" => 'Feature',
                'bbox' => $bbox,
                'geometry' => $geom,
            );
        } else {
            return $geom;
        }
    }

    /**
     * @param array $bbox
     * @return array
     */
    private static function toPolygonOrdinates(array $bbox)
    {
        return array(
            $bbox[0],
            $bbox[1],
            $bbox[2],
            $bbox[1],
            $bbox[2],
            $bbox[3],
            $bbox[0],
            $bbox[3],
            $bbox[0],
            $bbox[1],
        );
    }

    /**
     * @param array $ordinates
     * @return array
     */
    private static function toJsonCoordinates(array $ordinates)
    {
        $coords = [];
        for ($i = 1; $i < count($ordinates); $i += 2) {
            $coords[] = array($ordinates[$i - 1], $ordinates[$i]);
        }

        return $coords;
    }
}
