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
     */
    protected $slug;

    /**
     * @var string $source source
     * @ORM\Column(type="string", nullable=false)
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $keywords;

    /**
     * @var string $fees ServiceIdentification fees
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fees;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accessConstraints;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=false)
     */
    protected $providerName;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $providerSite;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $serviceContact;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $individualName;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $positionName;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phoneVoice;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phoneFacsimile;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $deliveryPoint;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $administrativeArea;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $postalCode;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $country;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $electronicMailAddress;

    /**
     * @var string $accessConstraints ServiceIdentification accessConstraints
     * @ORM\Column(type="string", nullable=true)
     */
    protected $onlineResourse;






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
     * Set serviceContact
     *
     * @param string $serviceContact
     *
     * @return Csw
     */
    public function setServiceContact($serviceContact)
    {
        $this->serviceContact = $serviceContact;

        return $this;
    }

    /**
     * Get serviceContact
     *
     * @return string
     */
    public function getServiceContact()
    {
        return $this->serviceContact;
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
}
