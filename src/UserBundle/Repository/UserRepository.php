<?php

namespace UserBundle\Repository;


class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByRole( $role ) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u')
            ->from($this->_entityName, 'u')
            ->where($qb->expr()->like('u.roles', ':roles'))
            ->setParameter('roles', '%"'.$role.'"%');
        return $qb->getQuery()->getResult();
    }

    public function findArbitre() {
        $qb = $this->createQueryBuilder( 'u' );

        $qb->where( $qb->expr()->orX()
            ->add( $qb->expr()->like( 'u.roles', ':role_admin' ) )
            ->add( $qb->expr()->like( 'u.roles', ':role_super_admin' ) )
            ->add( $qb->expr()->like( 'u.roles', ':role_arbitre' ) )
            ->add( $qb->expr()->like( 'u.roles', ':role_arbitre_waiting_approval' ) )
        );
        $qb->setParameter( 'role_admin', '%ROLE_ADMIN%' );
        $qb->setParameter( 'role_super_admin', '%ROLE_ARBITRE%' );
        $qb->setParameter( 'role_arbitre', '%ROLE_SUPER_ADMIN%' );
        $qb->setParameter( 'role_arbitre_waiting_approval', '%ROLE_ARBITRE_WAITING_APPROVAL%' );

        return $qb->getQuery()->getResult();
    }
}