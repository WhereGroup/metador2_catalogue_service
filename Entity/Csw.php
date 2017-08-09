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





}
