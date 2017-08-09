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
 * @Route("/csw/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="metador_admin_csw")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {

        $default = $this
            ->get('metador_configuration')
            ->getValues('plugin', 'metador_catalogue_service');


        dump($default);

        return array();
    }


    /**
     * @Route("/new/", name="metador_admin_csw_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction()
    {
        return array();
    }

    /**
     * @Route("/edit/{id}", name="metador_admin_csw_edit")
     * @Method({"GET", "POST"})
     * @Template("MetadorCoreBundle:Source:new.html.twig")
     * @param $id
     * @return array
     */
    public function editAction($id)
    {
        return array();
    }

    /**
     * @Route("/confirm/{id}", name="metador_admin_csw_confirm")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function confirmAction($id)
    {
        return array();
    }

    /**
     * @param $operation
     * @param $id
     * @param $message
     * @param array $parameter
     */
    private function setFlashWarning($operation, $id, $message, $parameter = array())
    {
        $log = $this->get('metador_logger')->newLog();

        $log->setType('warning')
            ->setFlashMessage()
            ->setCategory('application')
            ->setSubcategory('csw')
            ->setOperation($operation)
            ->setIdentifier($id)
            ->setMessage($message)
            ->setMessageParameter($parameter)
            ->setUsername($this->get('metador_user')->getUsernameFromSession());

        $this->get('metador_logger')->set($log);

        unset($log);
    }

    /**
     * @param $operation
     * @param $id
     * @param $message
     * @param array $parameter
     */
    private function setFlashSuccess($operation, $id, $message, $parameter = array())
    {
        $log = $this->get('metador_logger')->newLog();

        $log->setType('success')
            ->setFlashMessage()
            ->setCategory('application')
            ->setSubcategory('csw')
            ->setOperation($operation)
            ->setIdentifier($id)
            ->setMessage($message)
            ->setMessageParameter($parameter)
            ->setUsername($this->get('metador_user')->getUsernameFromSession());

        $this->get('metador_logger')->set($log);

        unset($log);
    }
}
