<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;

/**
 * The class GetCapabilities is a representation of the OGC CSW GetCapabilities operation.
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
final class GetCapabilities extends AOperation
{
    const SECTIONS = array('ServiceIdentification', 'ServiceProvider', 'OperationsMetadata', 'Filter_Capabilities');
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

//    /**
//     * {@inheritdoc}
//     */
//    protected $name = 'GetCapabilities';

//    /**
//     * The list with all supported capabilities sections
//     * @var array $sectionList
//     */
//    protected $sectionList;
//
//    /**
//     * The list with all supported capabilities operations
//     * @var array $sectionList
//     */
//    protected $operations;

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

    protected $urlBasic;

    protected $urlTransaction;

    /**
     * {@inheritdoc}
     */
    public function __construct(CswEntity $entity, $urlBasic, $urlTransaction)
    {
        parent::__construct($entity);
        $this->urlBasic = $urlBasic;
        $this->urlTransaction = $urlTransaction;
        $this->template = 'CatalogueServiceBundle:CSW:getcapabilities_response.xml.twig';
        $this->section = array();
//        $this->sectionList = $this->csw->getSections();
//        $this->operations  = array();
//        $operations        = $this->csw->getOperations();
//
//        foreach ($operations as $name => $value) {
//            if ($name !== $this->name) {
//                $class                   = $value['class'];
//                $this->operations[$name] = new $class($this->csw, $value);
//            } else {
//                $this->operations[$name] = $this;
//            }
//        }
//        $this->sectionList = array();
//        $sectionList       = $this->csw->getSections();
//        foreach ($sectionList as $name => $value) {
//            $class                    = $value['class'];
//            $this->sectionList[$name] = new $class($value);
//        }
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
//        unset(
//            $this->sectionList, $this->operations, $this->acceptVersion, $this->section
//        );
//        parent::__destruct();
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
//
//    /**
//     * Returns the sectionList.
//     * @return array sectionList
//     */
//    public function getSectionList()
//    {
//        return $this->sectionList;
//    }
//
//    /**
//     * Returns the operations
//     * @return array operations
//     */
//    public function getOperations()
//    {
//        return $this->operations;
//    }

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
                if (!in_array($item, self::SECTIONS)) {
                    $this->addCswException('section', CswException::InvalidParameterValue);
                }
            }
            $this->section = $sectionArr;
        }
    }
//
//    /**
//     * Returns all supported sections with needed parameters for a GetCapabilities document.
//     * @return array
//     */
//    public function getSections()
//    {
//        if (!$this->section) {
//            return $this->sectionList;
//        } else {
//            $sections = array();
//            foreach ($this->sectionList as $key => $value) {
//                if (in_array($key, $this->section)) {
//                    $sections[$key] = $value;
//                }
//            }
//            return $sections;
//        }
//    }
//
//    /**
//     * Sets the sectionList
//     * @param array $sectionList
//     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\GetCapabilities
//     */
//    public function setSectionList($sectionList)
//    {
//        $this->sectionList = $sectionList;
//        return $this;
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getParameters()
//    {
//        return array(
//            'sections' => array_keys($this->sectionList),
//        );
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getConstraints()
//    {
//        return array(
//            'PostEncoding' => $this->postEncodingList
//        );
//    }

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
            $this->exceptions[] = new CswException('acceptVersion', CswException::VersionNegotiationFailed);
        }
        parent::validateParameter();
    }

    protected function render($templating)
    {
        return $templating->render(
            $this->template,
            array(
                'getcap' => $this,
            )
        );
    }
}
