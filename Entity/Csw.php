<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Entity
 * @ORM\Table(name="csw")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository")
 */
class Csw
{

    /**
     * @var string $slug slug
     * @ORM\Id
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Slug darf nicht leer sein.")
     */
    protected $slug;

    /**
     * @var string $source source
     * @ORM\Id
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Quelle darf nicht leer sein.")
     */
    protected $source;

    /**
     * @var string $title ServiceIdentification title
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @var string $abstract ServiceIdentification abstract
     * @ORM\Column(type="text", nullable=true)
     */
    protected $abstract;

    /**
     * @var string $keywords ServiceIdentification keywords
     * @ORM\Column(type="array", nullable=false)
     */
    protected $keywords;

    /**
     * @var string $fees ServiceIdentification fees
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fees;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="array", nullable=false)
     */
    protected $accessConstraints;

    /**
     * @var string $providerName ServiceProvider providerName
     * @ORM\Column(type="string", nullable=false)
     * @Assert\NotBlank(message="Betreiber darf nicht leer sein.")
     */
    protected $providerName;

    /**
     * @var string $providerSite ServiceProvider providerSite
     * @ORM\Column(type="string", nullable=true)
     */
    protected $providerSite;

    /**
     * @var string $individualName ServiceProvider individualName
     * @ORM\Column(type="string", nullable=true)
     */
    protected $individualName;

    /**
     * @var string $positionName ServiceProvider positionName
     * @ORM\Column(type="string", nullable=true)
     */
    protected $positionName;

    /**
     * @var string $phoneVoice ServiceProvider $phoneVoicephoneVoice
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phoneVoice;

    /**
     * @var string $phoneFacsimile ServiceProvider phoneFacsimile
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phoneFacsimile;

    /**
     * @var string $deliveryPoint ServiceProvider deliveryPoint
     * @ORM\Column(type="string", nullable=true)
     */
    protected $deliveryPoint;

    /**
     * @var string $city ServiceProvider city
     * @ORM\Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string $administrativeArea ServiceProvider administrativeArea
     * @ORM\Column(type="string", nullable=true)
     */
    protected $administrativeArea;

    /**
     * @var string $postalCode ServiceProvider postalCode
     * @ORM\Column(type="string", nullable=true)
     */
    protected $postalCode;

    /**
     * @var string $country ServiceProvider country
     * @ORM\Column(type="string", nullable=true)
     */
    protected $country;

    /**
     * @var string $electronicMailAddress ServiceProvider electronicMailAddress
     * @ORM\Column(type="string", nullable=true)
     */
    protected $electronicMailAddress;

    /**
     * @var string $onlineResourse ServiceProvider onlineResourse
     * @ORM\Column(type="string", nullable=true)
     */
    protected $onlineResourse;

    /**
     * @var $doInsert boolean is a csw transaction insert supported.
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $doInsert = false;

    /**
     * @var $doUpdate boolean is a csw transaction update supported.
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $doUpdate = false;

    /**
     * @var $doDelete boolean is a csw transaction delete supported.
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $doDelete = false;

    public function __construct()
    {
        $this->keywords = array();
        $this->accessConstraints = array();
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Csw
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return Csw
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Csw
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set abstract
     *
     * @param string $abstract
     *
     * @return Csw
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract
     *
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Csw
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set fees
     *
     * @param string $fees
     *
     * @return Csw
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get fees
     *
     * @return string
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Set accessConstraints
     *
     * @param string $accessConstraints
     *
     * @return Csw
     */
    public function setAccessConstraints($accessConstraints)
    {
        $this->accessConstraints = $accessConstraints;

        return $this;
    }

    /**
     * Get accessConstraints
     *
     * @return string
     */
    public function getAccessConstraints()
    {
        return $this->accessConstraints;
    }

    /**
     * Set providerName
     *
     * @param string $providerName
     *
     * @return Csw
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;

        return $this;
    }

    /**
     * Get providerName
     *
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * Set providerSite
     *
     * @param string $providerSite
     *
     * @return Csw
     */
    public function setProviderSite($providerSite)
    {
        $this->providerSite = $providerSite;

        return $this;
    }

    /**
     * Get providerSite
     *
     * @return string
     */
    public function getProviderSite()
    {
        return $this->providerSite;
    }

    /**
     * Set individualName
     *
     * @param string $individualName
     *
     * @return Csw
     */
    public function setIndividualName($individualName)
    {
        $this->individualName = $individualName;

        return $this;
    }

    /**
     * Get individualName
     *
     * @return string
     */
    public function getIndividualName()
    {
        return $this->individualName;
    }

    /**
     * Set positionName
     *
     * @param string $positionName
     *
     * @return Csw
     */
    public function setPositionName($positionName)
    {
        $this->positionName = $positionName;

        return $this;
    }

    /**
     * Get positionName
     *
     * @return string
     */
    public function getPositionName()
    {
        return $this->positionName;
    }

    /**
     * Set phoneVoice
     *
     * @param string $phoneVoice
     *
     * @return Csw
     */
    public function setPhoneVoice($phoneVoice)
    {
        $this->phoneVoice = $phoneVoice;

        return $this;
    }

    /**
     * Get phoneVoice
     *
     * @return string
     */
    public function getPhoneVoice()
    {
        return $this->phoneVoice;
    }

    /**
     * Set phoneFacsimile
     *
     * @param string $phoneFacsimile
     *
     * @return Csw
     */
    public function setPhoneFacsimile($phoneFacsimile)
    {
        $this->phoneFacsimile = $phoneFacsimile;

        return $this;
    }

    /**
     * Get phoneFacsimile
     *
     * @return string
     */
    public function getPhoneFacsimile()
    {
        return $this->phoneFacsimile;
    }

    /**
     * Set deliveryPoint
     *
     * @param string $deliveryPoint
     *
     * @return Csw
     */
    public function setDeliveryPoint($deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;

        return $this;
    }

    /**
     * Get deliveryPoint
     *
     * @return string
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Csw
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set administrativeArea
     *
     * @param string $administrativeArea
     *
     * @return Csw
     */
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;

        return $this;
    }

    /**
     * Get administrativeArea
     *
     * @return string
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     *
     * @return Csw
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Csw
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set electronicMailAddress
     *
     * @param string $electronicMailAddress
     *
     * @return Csw
     */
    public function setElectronicMailAddress($electronicMailAddress)
    {
        $this->electronicMailAddress = $electronicMailAddress;

        return $this;
    }

    /**
     * Get electronicMailAddress
     *
     * @return string
     */
    public function getElectronicMailAddress()
    {
        return $this->electronicMailAddress;
    }

    /**
     * Set onlineResourse
     *
     * @param string $onlineResourse
     *
     * @return Csw
     */
    public function setOnlineResourse($onlineResourse)
    {
        $this->onlineResourse = $onlineResourse;

        return $this;
    }

    /**
     * Get onlineResourse
     *
     * @return string
     */
    public function getOnlineResourse()
    {
        return $this->onlineResourse;
    }

    /**
     * Set doInsert
     *
     * @param boolean $doInsert
     *
     * @return Csw
     */
    public function setDoInsert($doInsert)
    {
        $this->doInsert = $doInsert;

        return $this;
    }

    /**
     * Get doInsert
     *
     * @return boolean
     */
    public function getDoInsert()
    {
        return $this->doInsert;
    }

    /**
     * Set doUpdate
     *
     * @param boolean $doUpdate
     *
     * @return Csw
     */
    public function setDoUpdate($doUpdate)
    {
        $this->doUpdate = $doUpdate;

        return $this;
    }

    /**
     * Get doUpdate
     *
     * @return boolean
     */
    public function getDoUpdate()
    {
        return $this->doUpdate;
    }

    /**
     * Set doDelete
     *
     * @param boolean $doDelete
     *
     * @return Csw
     */
    public function setDoDelete($doDelete)
    {
        $this->doDelete = $doDelete;

        return $this;
    }

    /**
     * Get doDelete
     *
     * @return boolean
     */
    public function getDoDelete()
    {
        return $this->doDelete;
    }

    /**
     * Creates a Csw instance from properties.
     * @param array $kv csw properties
     * @return $this
     */
    public function fromArray(array $kv)
    {
        $this->slug = isset($kv['slug']) ? $kv['slug'] : null;
        $this->source = isset($kv['source']) ? $kv['source'] : null;
        $this->title = isset($kv['title']) ? $kv['title'] : null;
        $this->abstract = isset($kv['abstract']) ? $kv['abstract'] : null;
        $this->keywords = isset($kv['keywords']) ? $kv['keywords'] : array();
        $this->fees = isset($kv['fees']) ? $kv['fees'] : null;
        $this->accessConstraints = isset($kv['accessConstraints']) ? $kv['accessConstraints'] : array();
        $this->providerName = isset($kv['providerName']) ? $kv['providerName'] : null;
        $this->providerSite = isset($kv['providerSite']) ? $kv['providerSite'] : null;
        $this->individualName = isset($kv['individualName']) ? $kv['individualName'] : null;
        $this->positionName = isset($kv['positionName']) ? $kv['positionName'] : null;
        $this->phoneVoice = isset($kv['phoneVoice']) ? $kv['phoneVoice'] : null;
        $this->phoneFacsimile = isset($kv['phoneFacsimile']) ? $kv['phoneFacsimile'] : null;
        $this->deliveryPoint = isset($kv['deliveryPoint']) ? $kv['deliveryPoint'] : null;
        $this->city = isset($kv['city']) ? $kv['city'] : null;
        $this->administrativeArea = isset($kv['administrativeArea']) ? $kv['administrativeArea'] : null;
        $this->postalCode = isset($kv['postalCode']) ? $kv['postalCode'] : null;
        $this->country = isset($kv['country']) ? $kv['country'] : null;
        $this->electronicMailAddress = isset($kv['electronicMailAddress']) ? $kv['electronicMailAddress'] : null;
        $this->onlineResourse = isset($kv['onlineResourse']) ? $kv['onlineResourse'] : null;

        return $this;
    }
}
