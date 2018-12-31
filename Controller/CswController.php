<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\CswException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Exception\GetCapabilitiesNotFoundException;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\Parameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WhereGroup\CoreBundle\Component\Search\PropertyNameNotFoundException;

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
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig\Error\Error
     * @Route("{source}/{slug}", name="csw_default", methods={"GET", "POST"})
     */
    public function defaultAction(Request $request, $source, $slug)
    {
        $this->logRequest($request);

        /* @var Csw $csw */
        $csw = $this->get('metador_catalogue_service');

        /** @var \Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw $cswConfig */
        $cswConfig = $csw->findOneBySlugAndSource($slug, $source);

        if ($cswConfig === null) {
            throw new NotFoundHttpException('Csw mit slug:"'.$slug.'" und source:"'.$source.'" existiert nicht.');
        }

        if ($cswConfig->getActive() !== true) {
            return new Response('', Response::HTTP_NOT_FOUND);
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
                    throw new CswException('request', CswException::OPERATIONNOTSUPPORTED);
            }
        } catch (\Exception $ex) {
            return $this->renderException($ex);
        }

        return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml; charset=utf-8'));
    }

    /**
     * @param Request $request
     * @param $source
     * @param $slug
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig\Error\Error
     * @Route("manager/{source}/{slug}", name="csw_transaction", methods={POST"})
     */
    public function transactionAction(Request $request, $source, $slug)
    {
        $this->logRequest($request);

        /* @var Csw $csw */
        $csw = $this->get('metador_catalogue_service');
        $cswConfig = $csw->findOneBySlugAndSource($slug, $source);

        if ($cswConfig === null) {
            throw new NotFoundHttpException('Csw mit slug:"'.$slug.'" und source:"'.$source.'" existiert nicht.');
        }

        if ($cswConfig->getActive() !== true) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $content = null;
        try {
            /** @var Parameter $parameter */
            $parameter = $csw->readTransactionParameter($request->getContent());
            $content = $csw->transaction($parameter, $cswConfig);
        } catch (GetCapabilitiesNotFoundException $ex) {
            $url = $this->container->get('router')->generate("csw_default", ["source" => $source, "slug" => $slug]);
            $path = [
                "_controller" => "Plugins\WhereGroup\CatalogueServiceBundle\Controller\CswController::defaultAction",
                "source" => $source,
                "slug" => $slug,
                'url' => $url,
            ];
            $subRequest = new Request(
                $request->query->all(),
                $request->request->all(),
                $path,
                $request->cookies->all(),
                [],
                $request->server->all(),
                $request->getContent()
            );

            return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        } catch (\Exception $ex) {
            return $this->renderException($ex);
        }

        return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml; charset=utf-8'));
    }

    /**
     * @param \Exception $ex
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig\Error\Error
     */
    private function renderException(\Exception $ex)
    {
        if ($ex instanceof CswException) {
            $content = $this->get('templating')->render(
                "@CatalogueService/CSW/exception.xml.twig",
                array(
                    'exception' => array(
                        'code' => $ex->getCswCode(),
                        'locator' => $ex->getLocator(),
                        'text' => $ex->getText(),
                    ),
                )
            );

            return new Response($content, $ex->getHttpStatusCode(), array('content-type' => 'application/xml'));
        } elseif ($ex instanceof PropertyNameNotFoundException) {
            return $this->renderException(
                new CswException(
                    $ex->getMessage(),
                    CswException::DUPLICATESTOREDQUERYPARAMETERNAME
                )
            );
        } else {
            return $this->renderException(
                new CswException(
                    '',
                    CswException::NOAPPLICABLECODE,
                    $ex
                )
            );
        }
    }

    /**
     * @param Request $request
     */
    private function logRequest(Request $request) {
        if (!$this->container->hasParameter('log_csw') || $this->container->getParameter('log_csw') !== true) {
            return;
        }

        $time = microtime(true);
        $path = $this->get('kernel')->getRootDir() . '/../var/logs/'
            . date('Y-m-d_H-i-s_', $time)
            . substr(strstr($time, '.'), 1) . '_'
            . $request->getMethod() . '.log';

        $data =
            "\nMethod: " . $request->getMethod() .
            "\nURL: " . $request->getUri();

        if ($request->getMethod() !== 'GET') {
            $data .= "\nContent:\n" . $request->getContent();
        }

        file_put_contents($path, $data);
    }
}
