<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\EventListener;

use WhereGroup\CoreBundle\Event\ApplicationEvent;

/**
 * Class ApplicationListener
 * @package Plugins\WhereGroup\CatalogueService\EventListener
 */
class ApplicationListener
{
    /**
     * @param ApplicationEvent $event
     */
    public function onLoading(ApplicationEvent $event)
    {
        $app = $event->getApplication();

        if ($app->routeStartsWith('metador_admin')) {
            $app->add(
                $app->get('AdminMenu', 'csw')
                    ->icon('icon-cloud-download')
                    ->label('Catalogue Service')
                    ->path('metador_admin_csw')
                    ->setRole('ROLE_SYSTEM_SUPERUSER')
                    ->active($app->routeStartsWith('metador_admin_csw'))
            );
        }
    }
}
