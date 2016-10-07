<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;

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

        $xml = $this->get('catalogue_service')->getRecordById($uuid);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent($xml);

        return $response;
    }

    /**
     * @return Response
     * @Route("", name="csw_entry_point")
     * @Method({"GET", "POST"})
     */
    public function entrypointAction()
    {
        $response = new Response();
        try {
            $csw = $this->get('catalogue_service');
            $handler = $csw->getParameterHandler();
            $operation = $csw->getOperation($handler);
            $content = $csw->createContent($handler, $operation);

            $response->headers->set('Content-Type', $operation->getOutputFormat());
            $response->setContent($content);
        } catch (CswException $ex) {
            $content  = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array('exception' => array(
                    'code' => $ex->getCswCode(),
                    'locator' => $ex->getLocator(),
                    'text' => $ex->getText()
                )
            ));

            $response->headers->set('Content-Type', 'application/xml');
            $response->setContent($content);
        } catch (\Exception $ex) {
            $content  = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCode(),
                        'locator' => null,
                        'text' => array($ex->getMessage())
                    )
                )
            );

            $response->headers->set('Content-Type', 'application/xml');
            $response->setContent($content);
        }
        return $response;
    }
}