<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of FilterCapabilities
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class FilterCapabilities extends ASection
{
    protected $name = 'FilterCapabilities';

    protected $data = array(
        'spatial_Capabilities' => array(
            'geometryOperands' => array(
                'gml:Envelope',
                'gml:Point',
                'gml:LineString',
                'gml:Polygon'
            ),
            'spatialOperators' => array(
                'BBOX',
                'Beyond',
                'Contains',
                'Crosses',
                'Disjoint',
                'DWithin',
                'Equals',
                'Intersects',
                'Overlaps',
                'Touches',
                'Within'
            )
        ),
        'scalar_Capabilities' => array(
            'logicalOperators' => true,
            'comparisonOperators' => array(
                'Between',
                'EqualTo',
                'GreaterThan',
                'GreaterThanEqualTo',
                'LessThan',
                'LessThanEqualTo',
                'Like',
                'NotEqualTo',
                'NullCheck'
            )
        ),
        'id_Capabilities' => array('EID', 'FID')
    );
//
//    protected $spatialCapabilities = array(
//        'geometyOperands' => array('gml:Envelope', 'gml:Point', 'gml:LineString', 'gml:Polygon'));
//    protected $scalarCapabilities = array(
//        'logicalOperators' => array(),
//        'comparisonOperators' => array(
//            'EqualTo', 'Like', 'LessThan', 'GreaterThan', 'LessThanEqualTo',
//            'GreaterThanEqualTo', 'NotEqualTo', 'Between', 'NullCheck')
//    );
//    protected $idCapabilities = array('EID', 'FID');

    public function __construct($configuration = array())
    {
        //@TODO replace $spatialCapabilities, $scalarCapabilities, $idCapabilities with values from $configuration
    }

    public function getData()
    {
        return $this->data;
    }
//
//    public function getSpatialCapabilities()
//    {
//        return $this->spatialCapabilities;
//    }
//
//    public function getScalarCapabilities()
//    {
//        return $this->scalarCapabilities;
//    }
//
//    public function getIdCapabilities()
//    {
//        return $this->idCapabilities;
//    }
//
//    public function setSpatialCapabilities($spatialCapabilities)
//    {
//        $this->spatialCapabilities = $spatialCapabilities;
//        return $this;
//    }
//
//    public function setScalarCapabilities($scalarCapabilities)
//    {
//        $this->scalarCapabilities = $scalarCapabilities;
//        return $this;
//    }
//
//    public function setIdCapabilities($idCapabilities)
//    {
//        $this->idCapabilities = $idCapabilities;
//        return $this;
//    }
}