<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\EventListener;

use Exception;
use WhereGroup\CoreBundle\Event\ApplicationEvent;

/**
 * Class ApplicationListener
 * @package Plugins\WhereGroup\CatalogueService\EventListener
 */
class ApplicationListener
{
    /**
     * @param ApplicationEvent $event
     * @throws Exception
     */
    public function onLoading(ApplicationEvent $event)
    {
        $app = $event->getApplication();

        if ($app->routeStartsWith('metador_admin')) {
            $app->add(
                $app->get('AdminMenu', 'csw')
                    ->icon('icon-cloud-upload')
                    ->label('Catalogue Service')
                    ->path('metador_admin_csw')
                    ->setRole('ROLE_SYSTEM_SUPERUSER')
                    ->active($app->routeStartsWith('metador_admin_csw'))
            );
        }

        if ($app->routeStartsWith('manual')) {
            $app
                ->add(
                    $app->get('ManualMenu', 'csw')
                        ->icon('icon-cloud-download')
                        ->label('Catalogue Service')
                        ->path('manual_csw')
                        ->setRole('ROLE_SYSTEM_SUPERUSER')
                )
            ;
        }
    }
}
