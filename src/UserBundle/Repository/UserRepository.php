<?php

namespace UserBundle\Repository;


class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByRole( $role ) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where($qb->expr()->like('u.roles', ':roles'))
            ->setParameter('roles', '%"'.$role.'"%');
        return $qb->getQuery()->getResult();
    }
}