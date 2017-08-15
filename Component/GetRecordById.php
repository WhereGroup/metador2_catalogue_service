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
        'version' => '/csw:GetRecordById/@version',
        'service' => '/csw:GetRecordById/@service',
        'outputSchema' => '/csw:GetRecordById/@outputSchema',
        'outputFormat' => '/csw:GetRecordById/@outputFormat',
        'elementSetName' => '/csw:GetRecordById/csw:ElementSetName/text()',
        'id' => '/csw:GetRecordById/csw:Id/text()',
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
    public function __construct(\Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity = null)
    {
        parent::__construct($entity);
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return GetRecordById
     */
    public function setId($id)
    {
        if ($id && is_string($id)) {
            $this->id = $this->parseCsl($id);
        } elseif ($id && is_array($id)) {
            $this->id = $id;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'id':
                $this->setId($value);
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function render($templating)
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
                $xml .= "\n".$this->csw->getTemplating()->render(
                        $className.":Export:metadata.xml.twig",
                        array('p' => $record->getObject())
                    );
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
