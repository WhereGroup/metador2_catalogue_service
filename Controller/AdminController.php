<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\ContentSet;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Form\CswType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        return array(
            'services' => $this->get('metador_catalogue_service')->all(),
        );
    }


    /**
     * @Route("/new/", name="metador_admin_csw_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction()
    {
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        $cswDefaults = $this
            ->get('metador_configuration')
            ->getValues('plugin', 'metador_catalogue_service');
        $form = $this
            ->createForm($this->get("csw_form_type"), (new Csw())->fromArray($cswDefaults))
            ->handleRequest($this->get('request_stack')->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $entity Csw
             */
            $entity = $form->getData();

            if ($this->get('metador_catalogue_service')->findOneBySlugAndSource($entity->getSlug(),
                $entity->getSource())) {
                $this->setFlashWarning(
                    'new',
                    '',
                    'Catalogue Service existiert bereits.',
                    array()
                );

                return $this->redirectToRoute('metador_admin_csw');
            }

            $this->get('metador_catalogue_service')->save($entity);

            $this->setFlashSuccess(
                'new',
                $entity->getSlug(),
                'Catalogue Service %service% erfolgreich erstellt.',
                array('%service%' => $entity->getTitle())
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/edit/{source}/{slug}", name="metador_admin_csw_edit")
     * @Method({"GET", "POST"})
     * @Template("CatalogueServiceBundle:Admin:new.html.twig")
     * @param $slug
     * @return array
     */
    public function editAction($source, $slug)
    {
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        $form = $this
            ->createForm(CswType::class,
                $this->get('metador_catalogue_service')->findOneBySlugAndSource($slug, $source))
            ->handleRequest($this->get('request_stack')->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $entity Csw
             */
            $entity = $form->getData();

            $this->get('metador_catalogue_service')->save($entity);

            $this->setFlashSuccess(
                'edit',
                $entity->getSlug(),
                'Catalogue Service %service% erfolgreich editiert.',
                array('%service%' => $entity->getTitle())
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirm/{source}/{slug}", name="metador_admin_csw_confirm")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function confirmAction($source, $slug)
    {
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        $form = $this->createFormBuilder($this->get('metador_catalogue_service')->findOneBySlugAndSource($slug, $source))
            ->add('delete', 'submit', array(
                'label' => 'löschen',
            ))
            ->getForm()
            ->handleRequest($this->get('request_stack')->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var $entity Csw
             */
            $entity = $form->getData();
            $name = $entity->getTitle();
            $id = $entity->getSlug();

            $this->get('metador_source')->remove($entity);

            $this->setFlashSuccess(
                'edit',
                $id,
                'Csw %csw% erfolgreich gelöscht.',
                array('%csw%' => $id)
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return array(
            'form' => $form->createView(),
        );
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
