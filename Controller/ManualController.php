<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/public/manual")
 */
class ManualController extends Controller
{
    /**
     * @Route("/csw", name="manual_csw", methods={"GET"})
     */
    public function cswAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');
        return $this->render('@CatalogueService/Manual/csw.html.twig');
    }
}
