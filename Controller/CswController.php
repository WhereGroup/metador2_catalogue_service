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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Route("{source}/{slug}/", name="csw_default")
     * @Method({"GET", "POST"})
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\Error
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
                    throw new CswException('request', CswException::OPERATIONNOTSUPPORTED);
            }
        } catch (\Exception $ex) {
            return $this->renderException($ex);
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Twig\Error\Error
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
        } catch (\Exception $ex) {
            return $this->renderException($ex);
        }

        return new Response($content, Response::HTTP_OK, array('content-type' => 'application/xml'));
    }

    /**
     * @param \Exception $ex
     * @return Response
     * @throws \Twig\Error\Error
     */
    private function renderException(\Exception $ex)
    {
        if ($ex instanceof CswException) {
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
                    $ex->getMessage(),
                    CswException::NOAPPLICABLECODE
                )
            );
        }
    }
}
