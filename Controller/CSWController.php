<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CSWController
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Controller
 * @Route("/csw/")
 */
class CSWController extends Controller
{
    /**
     * @param $instance
     * @return Response
     * @Route("{instance}/", name="csw_get_record_by_id")
     * @Method({"GET", "POST"})
     */
    public function defaultAction($instance)
    {
        $uuid = $this->get('request_stack')->getCurrentRequest()->get('uuid');

        $xml  = $this->get('catalogue_service')->getRecordById($uuid);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent($xml);

        return $response;
    }
}
