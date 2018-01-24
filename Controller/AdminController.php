<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Plugins\WhereGroup\CatalogueServiceBundle\Component\ContentSet;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Form\CswType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class CSWController
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Controller
 * @Route("/admin/csw")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="metador_admin_csw")
     * @Method("GET")
     */
    public function indexAction()
    {
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        return $this->render('@CatalogueService/Admin/index.html.twig', array(
            'services' => $this->get('metador_catalogue_service')->all(),
        ));
    }


    /**
     * @Route("/new/", name="metador_admin_csw_new")
     * @Method({"GET", "POST"})
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
            /* @var Csw $csw */
            $csw = $form->getData();
            if ($this->get('metador_catalogue_service')->findOneBySlugAndSource($csw->getSlug(), $csw->getSource())) {
                $this->setFlash(
                    'warning',
                    'new',
                    '',
                    'Catalogue Service existiert bereits.',
                    array()
                );

                return $this->redirectToRoute('metador_admin_csw');
            }

            $this->get('metador_catalogue_service')->save($csw);

            $this->setFlash(
                'success',
                'new',
                $csw->getSlug(),
                'Catalogue Service %service% erfolgreich erstellt.',
                array('%service%' => $csw->getTitle())
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return $this->render('@CatalogueService/Admin/new.html.twig', array(
            'action' => 'new',
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{source}/{slug}", name="metador_admin_csw_edit")
     * @Method({"GET", "POST"})
     * @param string $source
     * @param string $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editAction($source, $slug)
    {
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');
        $cswInstance = $this->get('metador_catalogue_service')->findOneBySlugAndSource($slug, $source);

        $form = $this
            ->createForm(CswType::class, $cswInstance)
            ->handleRequest($this->get('request_stack')->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var Csw $entity */
            $entity = $form->getData();
            $this->get('metador_catalogue_service')->save($entity);

            $this->setFlash(
                'success',
                'edit',
                $entity->getSlug(),
                'Catalogue Service %service% erfolgreich editiert.',
                array('%service%' => $entity->getTitle())
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return $this->render('@CatalogueService/Admin/new.html.twig', array(
            'action' => 'edit',
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/confirm/{source}/{slug}", name="metador_admin_csw_confirm")
     * @Method({"GET", "POST"})
     * @param $source
     * @param $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function confirmAction($source, $slug)
    {
        $this->get('metador_core')->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');
        $cswInstance = $this->get('metador_catalogue_service')->findOneBySlugAndSource($slug, $source);
        $form = $this
            ->createFormBuilder($cswInstance)
            ->add('delete', SubmitType::class, array('label' => 'löschen'))
            ->getForm()
            ->handleRequest($this->get('request_stack')->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var Csw $entity */
            $entity = $form->getData();
            $id = $entity->getSlug();

            $this->get('metador_source')->remove($entity);

            $this->setFlash(
                'success',
                'edit',
                $id,
                'Csw %csw% erfolgreich gelöscht.',
                array('%csw%' => $id)
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return $this->render('@CatalogueService/Admin/confirm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param $type
     * @param $operation
     * @param $id
     * @param $message
     * @param array $parameter
     */
    private function setFlash($type, $operation, $id, $message, $parameter = array())
    {
        $log = $this->get('metador_logger')->newLog();

        $log->setType($type)
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
