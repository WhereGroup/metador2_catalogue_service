<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Symfony\Component\HttpFoundation\RequestStack;
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
    protected $templating = null;

    /**
     * Csw constructor.
     * @param RequestStack $requestStack
     * @param Metadata $metadata
     * @param Plugin $plugin
     * @param \Twig_Environment $twig
     */
    public function __construct(
        RequestStack $requestStack,
        Metadata $metadata,
        Plugin $plugin,
        \Twig_Environment $twig
    ) {
        $this->requestStack = $requestStack;
        $this->metadata = $metadata;
        $this->plugin = $plugin;
        $this->templating = $twig;
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
