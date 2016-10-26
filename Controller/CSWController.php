<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\ContentSet;

/**
 * Class CSWController
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Controller
 * @Route("/csw/")
 */
class CSWController extends Controller
{
//
//    /**
//     * @param $instance
//     * @return Response
//     * @Route("{instance}/", name="csw_get_record_by_id")
//     * @Method({"GET", "POST"})
//     */
//    public function defaultAction($instance)
//    {
//        $uuid = $this->get('request_stack')->getCurrentRequest()->get('uuid');
//
//        $xml = $this->get('catalogue_service')->getRecordById($uuid);
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'text/xml');
//        $response->setContent($xml);
//
//        return $response;
//    }

    /**
     * @return StreamedResponse
     * @Route("service/", name="csw_entry_point")
     * @Method({"GET", "POST"})
     */
    public function serviceAction()
    {
        try {
            $csw       = $this->get('catalogue_service');
            $operation = $csw->createOperation();
            $response = new StreamedResponse(null, Response::HTTP_OK, array('content-type' => 'application/xml'));
            $contentset = $operation->getContentSet();
            $response->setCallback(function () use ($contentset) {
                while ($contentset->next()) {
                    echo $contentset->getContent();
                    flush();
                }
            });
            return $response;
        } catch (CswException $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCswCode(),
                        'locator' => $ex->getLocator(),
                        'text' => $ex->getText()
                    )
                )
            );

            return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
        } catch (\Exception $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                'exception' => array(
                    'code' => $ex->getCode(),
                    'locator' => null,
                    'text' => array($ex->getMessage())
                )
                )
            );
            return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
        }
        return $response;
    }

    /**
     * @return StreamedResponse
     * @Route("harvest", name="csw_harvest")
     * @Method({"GET", "POST"})
     */
    public function harvestAction()
    {
        return $this->serviceAction();
    }
}
