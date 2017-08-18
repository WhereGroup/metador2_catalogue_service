<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostDomParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\TransactionParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\ContentSet;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WhereGroup\CoreBundle\Component\Source;

/**
 * Class CSWController
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Controller
 * @Route("/csw/")
 */
class CswController extends Controller
{
    /**
     * @param Request $request
     * @param $source
     * @param $slug
     * @Route("{source}/{slug}/", name="csw_default")
     * @Method({"GET", "POST"})
     * @return Response
     */
    public function defaultAction(Request $request, $source, $slug)
    {
        try {
            $cswService = $this->get('metador_catalogue_service');
            $entity = $cswService->findOneBySlugAndSource($slug, $source);
            if ($entity === null) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Csw mit slug:"'.$slug.'" und source:"'.$source.'" existiert nicht.');
        }
        $content = null;
        try {
//            $operation = $cswService->getOperation($entity);
//            /* a Transaction isn't accepted */
//            if ($operation instanceof Transaction) {
//                throw new CswException('url', CswException::OperationNotSupported);
//            }
//            if ($request->getMethod() === 'GET') {
//                $content = $cswService->doGet($entity, $request->query->all(), $this->get('templating'));
//            } else {
//                $content = $cswService->doPost($entity, $request->getContent(), $this->get('templating'));
//            }
            if ($request->getMethod() === 'GET') {
                $handler = new GetParameterHandler($request->query->all());
            } else {
                $handler = new PostDomParameterHandler($request->getContent());
            }
            $content = $cswService->doBasic($entity, $handler, $this->get('templating'));
        } catch (CswException $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCswCode(),
                        'locator' => $ex->getLocator(),
                        'text' => $ex->getText(),
                    ),
                )
            );
        } catch (\Exception $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCode(),
                        'locator' => null,
                        'text' => array($ex->getMessage()),
                    ),
                )
            );
        }

        return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
    }


    /**
     * @param Request $request
     * @param $source
     * @param $slug
     * @Route("manager/{source}/{slug}/", name="csw_manager")
     * @Method({"POST"})
     * @return Response
     */
    public function managerAction(Request $request, $source, $slug)
    {
        try {
            $cswService = $this->get('metador_catalogue_service');
            $entity = $cswService->findOneBySlugAndSource($slug, $source);
            if ($entity === null) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            // no entity found or exception at findOneBySlugAndSource
            throw new NotFoundHttpException('Csw mit slug:"'.$slug.'" und source:"'.$source.'" existiert nicht.');
        }
        $content = null;
        try {
            $handler = new TransactionParameterHandler($request->getContent());
            $content = $cswService->doTransaction($entity, $handler, $this->get('templating'));
        } catch (CswException $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCswCode(),
                        'locator' => $ex->getLocator(),
                        'text' => $ex->getText(),
                    ),
                )
            );
        } catch (\Exception $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCode(),
                        'locator' => null,
                        'text' => array($ex->getMessage()),
                    ),
                )
            );
        }

        return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
    }
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
     * @Route("service", name="csw_entry_point")
     * @Method({"GET", "POST"})
     */
    public function serviceAction()
    {
        try {
            $csw = $this->get('catalogue_service');
            $operation = $csw->createOperation();

            $xml = $operation->createResult();
            $response = new Response();
            $response->headers->set('Content-Type', 'text/xml');
            $response->setContent($xml);

            return $response;
        } catch (CswException $ex) {
            $content = $this->get('templating')->render(
                "CatalogueServiceBundle:CSW:exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCswCode(),
                        'locator' => $ex->getLocator(),
                        'text' => $ex->getText(),
                    ),
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
                        'text' => array($ex->getMessage()),
                    ),
                )
            );

            return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
        }

        return $response;
    }

    /**
     * @return StreamedResponse
     * @Route("streamed", name="csw_entry_point_streamed")
     * @Method({"GET", "POST"})
     */
    public function streamedAction()
    {
        try {
            $csw = $this->get('catalogue_service');
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
                        'text' => $ex->getText(),
                    ),
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
                        'text' => array($ex->getMessage()),
                    ),
                )
            );

            return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
        }

        return $response;
    }
}
