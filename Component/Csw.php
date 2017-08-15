<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Component;

use Doctrine\ORM\EntityManagerInterface;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostDomParameterHandler;
use Plugins\WhereGroup\CatalogueServiceBundle\Entity\Csw as CswEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use WhereGroup\CoreBundle\Entity\Source;

//use Symfony\Component\DependencyInjection\ContainerAwareTrait;
//use Symfony\Component\DependencyInjection\ContainerInterface;
//use Symfony\Component\HttpFoundation\RequestStack;
//use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;
//use WhereGroup\CoreBundle\Component\Metadata;
//use WhereGroup\PluginBundle\Component\Plugin;
//use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\PostSaxParameterHandler;
//use Plugins\WhereGroup\CatalogueServiceBundle\Component\Parameter\GetParameterHandler;

/**
 * Class Csw
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Component
 */
class Csw
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|null|\Plugins\WhereGroup\CatalogueServiceBundle\Entity\CswRepository
     */
    protected $repo = null;


    const ENTITY = "CatalogueServiceBundle:Csw";

    /**
     * @var null|RequestStack
     */
    protected $requestStack = null;

    /**
     * Csw constructor.
     * @param EntityManagerInterface $em
     * @param RequestStack $requestStack
     * @param RouterInterface $router
     */
    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, RouterInterface $router)
    {
        $this->repo = $em->getRepository(self::ENTITY);
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset(
            $this->repo,
            $this->requestStack,
            $this->router
        );
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return (int)$this->repo->count();
    }

    /**
     * @return array|\WhereGroup\CoreBundle\Entity\Source[]
     */
    public function all()
    {
        return $this->repo->findAll();
    }

    /**
     * @param $slug
     * @param $source
     * @return mixed
     */
    public function findOneBySlugAndSource($slug, $source)
    {
        return $this->repo->findOneBySlugAndSource($slug, $source);
    }

    /**
     * @param $entity
     * @return $this
     */
    public function save($entity)
    {
        $this->repo->save($entity);

        return $this;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function remove($entity)
    {
        $this->repo->remove($entity);

        return $this;
    }

    /**
     * Creates an operation for a given operation name
     *
     * @param string $operationName
     * @param CswEntity $entity
     * @return AOperation
     * @throws CswException
     */
    public function operationForName($operationName, CswEntity $entity)
    {
        switch ($operationName) {
            case 'GetCapabilities':
                $req = $this->requestStack->getCurrentRequest();
                $urlBasic = $this->router->generate('csw_default', array(
                    'source' => $entity->getSource(),
                    'slug' => $entity->getSlug(),
                ),
                    UrlGeneratorInterface::ABSOLUTE_URL);
                $urlManager = $this->router->generate('csw_manager', array(
                    'source' => $entity->getSource(),
                    'slug' => $entity->getSlug(),
                ),
                    UrlGeneratorInterface::ABSOLUTE_URL);

                return new GetCapabilities($entity, $urlBasic, $urlManager);
            case 'DescribeRecord':
                return new DescribeRecord($entity);
            case 'GetRecordById':
                return new GetRecordById($entity);
            case 'GetRecords':
                return new GetRecords($entity);
            default:
                throw new CswException('request', CswException::OperationNotSupported);
        }
    }

    /**
     * Creates an operation
     * @return \Plugins\WhereGroup\CatalogueServiceBundle\Component\AOperation
     */
    public function getBasicOperation(CswEntity $entity)
    {
        $handler = null;
        $request = $this->requestStack->getCurrentRequest();
        $operation = null;
        if ($request->getMethod() === 'GET') {
            $handler = new GetParameterHandler($this->requestStack->getCurrentRequest()->query->all());
            $operationName = $handler->getOperationName();
            $operation = $handler->initOperation($this->operationForName($operationName, $entity));

            return $operation;
        } elseif ($request->getMethod() === 'POST') {
            $handler = new PostDomParameterHandler($request->getContent());
            $operationName = $handler->getOperationName();
            $operation = $handler->initOperation($this->operationForName($operationName, $entity));
        }
        return $operation;
    }
}
