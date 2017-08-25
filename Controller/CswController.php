<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\Parameter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        /* @var Csw $csw */
        $csw = $this->get('metador_catalogue_service');
        $cswConfig = $csw->findOneBySlugAndSource($slug, $source);

        if ($cswConfig === null) {
            throw new NotFoundHttpException('Csw mit slug:"'.$slug.'" und source:"'.$source.'" existiert nicht.');
        }

        $content = null;

        try {
            /*  @var Parameter $parameter */
            $parameter = null;
            if ($request->getMethod() === 'GET') {
                $parameter = $csw->readGetParameter($request->query->all());
            } else {
                $parameter = $csw->readPostParameter($request->getContent());
            }

            switch ($parameter->getOperationName()) {
                case 'GetCapabilities':
                    $params = array(
                        'source' => $cswConfig->getSource(),
                        'slug' => $cswConfig->getSlug(),
                    );
                    $content = $csw->getCapabilities(
                        $parameter,
                        $cswConfig,
                        $this->get('router')->generate('csw_default', $params, UrlGeneratorInterface::ABSOLUTE_URL),
                        $this->get('router')->generate('csw_transaction', $params, UrlGeneratorInterface::ABSOLUTE_URL)
                    );
                    break;
                case 'DescribeRecord':
                    $content = $csw->describeRecord($parameter, $cswConfig);
                    break;
                case 'GetRecordById':
                    $content = $csw->getRecordById($parameter, $cswConfig);
                    break;
                case 'GetRecords':
                    $content = $csw->getRecords($parameter, $cswConfig);
                    break;
                default:
                    throw new CswException('request', CswException::OperationNotSupported);

            }
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
     * @Route("manager/{source}/{slug}/", name="csw_transaction")
     * @Method({"POST"})
     * @return Response
     */
    public function transactionAction(Request $request, $source, $slug)
    {
        /* @var Csw $csw */
        $csw = $this->get('metador_catalogue_service');
        $cswConfig = $csw->findOneBySlugAndSource($slug, $source);

        if ($cswConfig === null) {
            throw new NotFoundHttpException('Csw mit slug:"'.$slug.'" und source:"'.$source.'" existiert nicht.');
        }

        $content = null;
        try {
            /*  @var Parameter $parameter */
            $parameter = $csw->readTransactionParameter($request->getContent());
            $content = $csw->transaction($parameter, $cswConfig);
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
