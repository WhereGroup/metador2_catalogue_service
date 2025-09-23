<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw;
use Plugins\WhereGroup\CatalogueServiceBundle\Form\CswType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     * @Route("/", name="metador_admin_csw", methods={"GET"})
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        return $this->render('@CatalogueService/Admin/index.html.twig', [
            'services' => $this->get('metador_catalogue_service')->all(),
        ]);
    }


    /**
     * @Route("/new/", name="metador_admin_csw_new", methods={"GET", "POST"})
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function newAction()
    {
        $this->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');

        $cswDefaults = $this
            ->get('metador_configuration')
            ->getValues('plugin', 'metador_catalogue_service');

        $form = $this
            ->createForm(CswType::class, (new Csw())->fromArray($cswDefaults))
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
                    []
                );

                return $this->redirectToRoute('metador_admin_csw');
            }

            $this->get('metador_catalogue_service')->save($csw);

            $this->setFlash(
                'success',
                'new',
                $csw->getSlug(),
                'Catalogue Service %service% erfolgreich erstellt.',
                ['%service%' => $csw->getTitle()]
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return $this->render('@CatalogueService/Admin/new.html.twig', [
            'action' => 'new',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{source}/{slug}", name="metador_admin_csw_edit", methods={"GET", "POST"})
     * @param string $source
     * @param string $slug
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction($source, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');
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
                ['%service%' => $entity->getTitle()]
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return $this->render('@CatalogueService/Admin/new.html.twig', [
            'action' => 'edit',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{source}/{slug}", name="metador_admin_csw_confirm", methods={"GET", "POST"})
     * @param $source
     * @param $slug
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function confirmAction($source, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_SYSTEM_SUPERUSER');
        $cswInstance = $this->get('metador_catalogue_service')->findOneBySlugAndSource($slug, $source);
        $form = $this
            ->createFormBuilder($cswInstance)
            ->add('delete', SubmitType::class, ['label' => 'löschen'])
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
                ['%csw%' => $id]
            );

            return $this->redirectToRoute('metador_admin_csw');
        }

        return $this->render('@CatalogueService/Admin/confirm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param $type
     * @param $operation
     * @param $id
     * @param $message
     * @param array $parameter
     */
    private function setFlash($type, $operation, $id, $message, $parameter = [])
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
