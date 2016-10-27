<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * The class GetCapabilities is a representation of the OGC CSW GetCapabilities operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
final class GetCapabilities extends AOperation
{
    /**
     * {@inheritdoc}
     */
    protected static $parameterMap = array(
        'version'       => null,
        'service'       => '/' . Csw::CSW_PREFIX . ':GetCapabilities/@service',
        'acceptVersion' => '/' . Csw::CSW_PREFIX . ':GetCapabilities/ows:AcceptVersions/ows:Version/text()',
        'section'       => '/' . Csw::CSW_PREFIX . ':GetCapabilities/ows:Sections/ows:Section/text()',
        'outputFormat'  => '/' . Csw::CSW_PREFIX . ':GetCapabilities/ows:AcceptFormats/ows:OutputFormat/text()',
    );

    /**
     * {@inheritdoc}
     */
    protected $name = 'GetCapabilities';

    /**
     * The list with all supported capabilities sections
     * @var array $sectionList
     */
    protected $sectionList;

    /**
     * The list with all supported capabilities operations
     * @var array $sectionList
     */
    protected $operations;

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
     * {@inheritdoc}
     */
    public function __construct(Csw $csw, $configuration)
    {
        parent::__construct($csw, $configuration);
        $this->sectionList = $this->csw->getSections();
        $this->operations  = array();
        $operations        = $this->csw->getOperations();

        foreach ($operations as $name => $value) {
            if ($name !== $this->name) {
                $class                   = $value['class'];
                $this->operations[$name] = new $class($this->csw, $value);
            } else {
                $this->operations[$name] = $this;
            }
        }
        $this->sectionList = array();
        $sectionList       = $this->csw->getSections();
        foreach ($sectionList as $name => $value) {
            $class                    = $value['class'];
            $this->sectionList[$name] = new $class($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        unset(
            $this->sectionList, $this->operations, $this->acceptVersion, $this->section
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
        $parameters       = array();
        foreach (self::$parameterMap as $key => $value) {
            if ($value !== null) {
                $parameters[$value] = $key;
            }
        }
        return $parameters;
    }

    /**
     * Returns the sectionList.
     * @return array sectionList
     */
    public function getSectionList()
    {
        return $this->sectionList;
    }

    /**
     * Returns the operations
     * @return array operations
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Returns the acceptVersion
     * @return array acceptVersion
     */
    public function getAcceptVersion()
    {
        return $this->acceptVersion;
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
     * Returns all supported sections with needed parameters for a GetCapabilities document.
     * @return array
     */
    public function getSections()
    {
        if (!$this->section) {
            return $this->sectionList;
        } else {
            $sections = array();
            foreach ($this->sectionList as $key => $value) {
                if (in_array($key, $this->section)) {
                    $sections[$key] = $value;
                }
            }
            return $sections;
        }
    }

    /**
     * Sets the sectionList
     * @param array $sectionList
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\GetCapabilities
     */
    public function setSectionList($sectionList)
    {
        $this->sectionList = $sectionList;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array(
            'sections' => array_keys($this->sectionList),
        );
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
            case 'acceptVersion':
                if ($value && is_string($value)) { # GET request
                    $this->acceptVersion = self::parseCsl($value);
                } elseif ($value && is_array($value)) { # POST request
                    $this->acceptVersion = $value;
                }
                break;
            case 'section':
                $section = array();
                if ($value && is_string($value)) { # GET request
                    $section = self::parseCsl($value);
                } elseif ($value && is_array($value)) { # POST request or parsed GET
                    $section = $value;
                }
                if (count($section) > 0) {
                    foreach ($section as $item) {
                        if (!isset($this->sectionList[$item])) {
                            $this->addCswException('section', CswException::InvalidParameterValue);
                        }
                    }
                    $this->section = $section;
                }
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
            $this->exceptions[] = new CswException('acceptVersion', CswException::VersionNegotiationFailed);
        }
        parent::validateParameter();
    }

    protected function render()
    {
        return $this->csw->getTemplating()->render(
                $this->templates[$this->getOutputFormat()],
                array(
                'getcapabilities' => $this
                )
        );
    }
}
