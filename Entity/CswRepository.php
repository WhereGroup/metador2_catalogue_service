<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * Class CswRepository
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Entity
 */
class CswRepository extends EntityRepository
{
    /**
     * @return mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countAll()
    {
        return $this
            ->getEntityManager()
            ->getRepository("CatalogueServiceBundle:Csw")
            ->createQueryBuilder('u')
            ->select('count(u.slug)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $slug
     * @param $source
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBySlugAndSource($slug, $source)
    {
        try {
            return $this
                ->createQueryBuilder('c')
                ->select('c')
                ->where('c.slug = :slug AND c.source = :source')
                ->setParameters(array('slug' => $slug, 'source' => $source))
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param $entity
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @param $entity
     * @return $this
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return $this;
    }
}
