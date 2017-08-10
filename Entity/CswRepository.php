<?php

namespace Plugins\WhereGroup\CatalogueServiceBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class CswRepository
 * @package Plugins\WhereGroup\CatalogueServiceBundle\Entity
 */
class CswRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function count()
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
     * @param $entity
     * @return $this
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
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return $this;
    }

}
