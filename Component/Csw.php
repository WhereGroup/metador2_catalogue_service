<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
use WhereGroup\CoreBundle\Component\Metadata;
use WhereGroup\PluginBundle\Component\Plugin;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 */
class Csw
{
    protected $requestStack = null;
    protected $metadata = null;
    protected $plugin = null;

    /** @var TimedTwigEngine $templating */
    protected $templating = null;

    /**
     * Csw constructor.
     * @param RequestStack $requestStack
     * @param Metadata $metadata
     * @param Plugin $plugin
     * @param $templating
     */
    public function __construct(
        RequestStack $requestStack,
        Metadata $metadata,
        Plugin $plugin,
        $templating
    ) {
        $this->requestStack = $requestStack;
        $this->metadata = $metadata;
        $this->plugin = $plugin;
        $this->templating = $templating;
    }

    public function __destruct()
    {
        unset(
            $this->requestStack,
            $this->metadata,
            $this->plugin,
            $this->templating
        );
    }

    /**
     * @param $id
     * @return string
     */
    public function getRecordById($id)
    {
        /** @var \WhereGroup\CoreBundle\Entity\Metadata $entity */
        $entity = $this->metadata->getByUUID($id);

        // get data object
        $p = $entity->getObject();

        // get profile
        $className = $this->plugin->getPluginClassName($p['_profile']);

        // render metadata
        return $this->templating->render($className .":Export:metadata.xml.twig", array(
            "p" => $p
        ));
    }
}
