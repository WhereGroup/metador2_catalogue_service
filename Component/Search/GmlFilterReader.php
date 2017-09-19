<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component\Search;

use WhereGroup\CoreBundle\Component\Search\Expression;
use WhereGroup\CoreBundle\Component\Search\ExprHandler;
use WhereGroup\CoreBundle\Component\Search\FilterReader;

/**
 * Class GmlFilterReader
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component\Search
 * @author Paul Schmidt <panadium@gmx.de>
 */
class GmlFilterReader implements FilterReader
{

    /**
     * @param mixed $filter
     * @param ExprHandler $expression
     * @return null|Expression
     */
    public static function read($filter, ExprHandler $expression)
    {
        $parameters = array();
        $expression = self::getExpression($filter, $expression, $parameters);
        if (is_array($expression) && count($expression) === 0) {
            return null;
        } else {
            return new Expression($expression, $parameters);
        }
    }

    /**
     * @param \DOMElement $filter
     * @param ExprHandler $expression
     * @param $parameters
     * @return array|mixed|null
     */
    private static function getExpression(\DOMElement $filter, ExprHandler $expression, &$parameters)
    {
        $items = array();
        /* @var \DOMElement $child */
        $child = null;
        foreach ($filter->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            switch ($child->localName) {
                case 'And':
                    $list = self::getExpression($child, $expression, $parameters);

                    if (count($list) > 1) {
                        return $expression->andx($list);
                    } elseif (count($list) === 1) {
                        return $list[0];
                    }

                    return null;
                case 'Or':
                    $list = self::getExpression($child, $expression, $parameters);

                    if (count($list) > 1) {
                        return $expression->orx($list);
                    } elseif (count($list) === 1) {
                        return $list[0];
                    }

                    return null;
                case 'Not':
                    $item = self::getExpression($child, $expression, $parameters);

                    return $expression->not($item);
                case 'PropertyIsEqualTo':
                    $operands = self::getComparisonContent($child);

                    return $expression->eq($operands['name'], $operands['literal'], $parameters);
                case 'PropertyIsNotEqualTo':
                    $operands = self::getComparisonContent($child);

                    return $expression->neq($operands['name'], $operands['literal'], $parameters);
                case 'PropertyIsLike':
                    $operands = self::getComparisonContent($child);

                    return $expression->like(
                        $operands['name'],
                        $operands['literal'],
                        $parameters,
                        $child->getAttribute('escapeChar'),
                        $child->getAttribute('singleChar'),
                        $child->getAttribute('wildCard')
                    );
                case 'PropertyIsBetween':
                    $operands = self::getBetweenContent($child);

                    return $expression->between($operands['name'], $operands['lower'], $operands['upper'], $parameters);
                case 'PropertyIsGreaterThan':
                    $operands = self::getGtLtContent($child);

                    return $expression->gt($operands['name'], $operands['literal'], $parameters);
                case 'PropertyIsGreaterThanOrEqualTo':
                    $operands = self::getGtLtContent($child);

                    return $expression->gte($operands['name'], $operands['literal'], $parameters);
                case 'PropertyIsLessThan':
                    $operands = self::getGtLtContent($child);

                    return $expression->lt($operands['name'], $operands['literal'], $parameters);
                case 'PropertyIsLessThanOrEqualTo':
                    $operands = self::getGtLtContent($child);

                    return $expression->lte($operands['name'], $operands['literal'], $parameters);
                case 'PropertyIsNull':
                    $operands = self::getComparisonContent($child);

                    return $expression->isNull($operands['name']);
                case 'BBOX':
                case 'Intersects':
                    $operands = self::getSpatialContent($child);

                    return $expression->bbox($operands['name'], $operands['geom'], $parameters);
                case 'Contains':
                    $operands = self::getSpatialContent($child);

                    return $expression->contains($operands['name'], $operands['geom'], $parameters);
                case 'Within':
                    $operands = self::getSpatialContent($child);

                    return $expression->within($operands['name'], $operands['geom'], $parameters);
                default:
                    return null;
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
        $operands = array();
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
        $operands = array();
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
        $operands = array();
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
        $operands = array();
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
                    $coords = array();
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
        $coords = array();
        for ($i = 1; $i < count($ordinates); $i += 2) {
            $coords[] = array($ordinates[$i - 1], $ordinates[$i]);
        }

        return $coords;
    }
}
