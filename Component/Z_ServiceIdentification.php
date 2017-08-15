<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

/**
 * Description of ServiceIdentification
 *
 * @author Paul Schmidt<panadium@gmx.de>
 */
class ServiceIdentification extends ASection
{
    protected $name = 'ServiceIdentification';

    //@TODO inplement variables ???
    protected $data;
//    protected $title;
//    protected $abstract;
//    protected $keywords;
//    protected $versions;

    public function __construct($configuration)
    {
        $this->data = $configuration;
        unset($this->data['class']);
//        $this->title    = $configuration['title'];
//        $this->abstract = $configuration['abstract'];
//        $this->keywords = $configuration['keywords'];
//        $this->versions = $configuration['versions'];
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }



//    public function getTitle()
//    {
//        return $this->title;
//    }
//
//    public function getAbstract()
//    {
//        return $this->abstract;
//    }
//
//    public function getKeywords()
//    {
//        return $this->keywords;
//    }
//
//    public function getVersions()
//    {
//        return $this->versions;
//    }
//
//    public function setTitle($title)
//    {
//        $this->title = $title;
//        return $this;
//    }
//
//    public function setAbstract($abstract)
//    {
//        $this->abstract = $abstract;
//        return $this;
//    }
//
//    public function setKeywords($keywords)
//    {
//        $this->keywords = $keywords;
//        return $this;
//    }
//
//    public function setVersions($versions)
//    {
//        $this->versions = $versions;
//        return $this;
//    }
}