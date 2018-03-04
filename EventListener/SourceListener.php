<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use WhereGroup\CoreBundle\Event\SourceEvent;

/**
 * Class SourceListener
 * @package Plugins\WhereGroup\CatalogueServiceBundle\EventListener
 */
class SourceListener
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     * | null
     * | \Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository
     */
    protected $repo = null;

    const ENTITY = "CatalogueServiceBundle:Csw";

    /** @param EntityManagerInterface $em */
    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(self::ENTITY);
    }

    public function __destruct()
    {
        unset($this->repo);
    }

    /**
     * @param SourceEvent $event
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function onConfirm(SourceEvent $event)
    {
        $count = $this->repo->countBySource($event->getSlug());

        if ($count > 0) {
            $event->addMessage("Es werden %count% CSW-Schnittstellen aufgelöst.", ['%count%' => $count]);
        }
    }

    /**
     * @param SourceEvent $event
     */
    public function onPostDelete(SourceEvent $event)
    {
        $this->repo->deleteBySource($event->getSlug());
    }
}
