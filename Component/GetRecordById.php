<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * The class GetRecordById is a representation of the OGC CSW GetCapabilities operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class GetRecordById extends AFindRecord
{
    /**
     * {@inheritdoc}
     */
    static $parameterMap = array(
        'version' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@version',
        'service' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@service',
        'outputSchema' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@outputSchema',
        'outputFormat' => '/' . Csw::CSW_PREFIX . ':GetRecordById/@outputFormat',
        'elementSetName' => '/' . Csw::CSW_PREFIX . ':GetRecordById/' . Csw::CSW_PREFIX . ':ElementSetName/text()',
        'id' => '/' . Csw::CSW_PREFIX . ':GetRecordById/' . Csw::CSW_PREFIX . ':Id/text()',
    );
    protected $outputSchema;
    protected $id;

    /**
     * {@inheritdoc}
     */
    protected $name = 'GetRecordById';

    /**
     * {@inheritdoc}
     */
    public function __construct(Csw $csw = null, $configuration = array())
    {
        parent::__construct($csw, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        unset(
            $this->outputSchema, $this->id
        );
        parent::__destruct();
    }

    /**
     * {@inheritdoc}
     */
    public static function getGETParameterMap()
    {
        return array_keys(self::$parameterMap);
    }

    /**
     * {@inheritdoc}
     */
    public static function getPOSTParameterMap()
    {
        $parameters = array();
        foreach (self::$parameterMap as $key => $value) {
            if ($value !== null) {
                $parameters[$value] = $key;
            }
        }
        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array_merge(parent::getParameters(),
            array(
            'ElementSetName' => $this->elementSetNameList
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        return array(
            'PostEncoding' => $this->postEncodingList
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'id':
                if ($value && is_string($value)) {
                    $this->id = $this->parseCsl($value);
                } elseif ($value && is_array($value)) {
                    $this->id = $value;
                }
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function render()
    {
        $xml = '';

        try {
            foreach ($this->id as $id) {
                $record = $this->csw->getMetadata()->getByUUID($id);

                if (!$record->getPublic()) {
                    // TODO: maby exception
                    continue;
                }

                // GET Template
                $className = $this->csw->container->get('metador_plugin')->getPluginClassName($record->getProfile());
                $xml .= "\n" . $this->csw->getTemplating()->render($className .":Export:metadata.xml.twig", $record);
            }
        } catch (\Exception $e) {
            throw new CswException('id', CswException::NoApplicableCode);
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<csw:GetRecordByIdResponse xmlns:csw=\"http://www.opengis.net/cat/csw/2.0.2\">
$xml
</csw:GetRecordByIdResponse>";
    }
}
