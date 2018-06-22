<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * The class GetCapabilities is a representation of the OGC CSW GetCapabilities operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
final class GetCapabilities extends Operation
{
    public static $SECTIONS = array(
        'ServiceIdentification',
        'ServiceProvider',
        'OperationsMetadata',
        'Filter_Capabilities',
    );
    /**
     * {@inheritdoc}
     */
    protected static $parameterMap = array(
        'version' => null,
        'service' => '/csw:GetCapabilities/@service',
        'acceptVersion' => '/csw:GetCapabilities/ows:AcceptVersions/ows:Version/text()',
        'section' => '/csw:GetCapabilities/ows:Sections/ows:Section/text()',
        'outputFormat' => '/csw:GetCapabilities/ows:AcceptFormats/ows:OutputFormat/text()',
    );

    /**
     * @var string url for csw basic
     */
    protected $urlBasic;

    /**
     * @var string url for csw transaction
     */
    protected $urlTransaction;

    /* Request parameters */

    /**
     * The request parameter "acceptVersion"
     * @var array $acceptVersion
     */
    protected $acceptVersion;

    /**
     * The request parameter "section"
     * @var array $section
     */
    protected $section;

    /**
     * The list of supported ISO queryables
     * @var array $isoqueryables
     */
    protected $isoqueryables;

    /**
     * The list of supported additional queryables
     * @var array $addedqueryables
     */
    protected $addedqueryables;

    /**
     * {@inheritdoc}
     */
    public function __construct(\Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $entity, $urlBasic, $urlTAction)
    {
        parent::__construct($entity);
        $this->urlBasic = $urlBasic;
        $this->urlTransaction = $urlTAction;
        $this->section = array();
        $this->isoqueryables = self::ISO_QUERYABLES;
        $this->addedqueryables = self::ADDITIONAL_QUERYABLES;
    }

    /**
     * @return array
     */
    public function getIsoqueryables()
    {
        return $this->isoqueryables;
    }

    /**
     * @return array
     */
    public function getAddedqueryables()
    {
        return $this->addedqueryables;
    }

    /**
     * @return mixed
     */
    public function getUrlBasic()
    {
        return $this->urlBasic;
    }

    /**
     * @param mixed $urlBasic
     * @return GetCapabilities
     */
    public function setUrlBasic($urlBasic)
    {
        $this->urlBasic = $urlBasic;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrlTransaction()
    {
        return $this->urlTransaction;
    }

    /**
     * @param mixed $urlTransaction
     * @return GetCapabilities
     */
    public function setUrlTransaction($urlTransaction)
    {
        $this->urlTransaction = $urlTransaction;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGETParameterMap()
    {
        return array_keys(self::$parameterMap);
    }

    /**
     * {@inheritdoc}
     */
    public function getPOSTParameterMap()
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
     * Returns the acceptVersion
     * @return array acceptVersion
     */
    public function getAcceptVersion()
    {
        return $this->acceptVersion;
    }

    public function setAcceptVersion($value)
    {
        if ($value && is_string($value)) { # GET request
            $this->acceptVersion = self::parseCsl($value);
        } elseif ($value && is_array($value)) { # POST request
            $this->acceptVersion = $value;
        }
    }

    /**
     * Returns the section
     * @return array
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section)
    {
        $sectionArr = array();
        if ($section && is_string($section)) { # GET request
            $sectionArr = self::parseCsl($section);
        } elseif ($section && is_array($section)) { # POST request or parsed GET
            $sectionArr = $section;
        }
        if (count($sectionArr) > 0) {
            foreach ($sectionArr as $item) {
                if (!in_array($item, self::$SECTIONS)) {
                    $this->addCswException('section', CswException::INVALIDPARAMETERVALUE);
                }
            }
            $this->section = $sectionArr;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        switch ($name) {
            case 'acceptVersion':
                $this->setAcceptVersion($value);
                break;
            case 'section':
                $this->setSection($value);
                break;
            default:
                parent::setParameter($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateParameter()
    {
        if ($this->acceptVersion && !in_array($this->version, $this->acceptVersion)) {
            $this->exceptions[] = new CswException('acceptVersion', CswException::VERSIONNEGOTIATIONFAILED);
        }
        parent::validateParameter();
    }
}
