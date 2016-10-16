<?php

namespace MatchBundle\Repository;

/**
 * MatchsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MatchsRepository extends \Doctrine\ORM\EntityRepository
{
    function findByTeam( $id, $ordered = false ) {
        $qb = $this->createQueryBuilder( 'm' );
        $qb->where( $qb->expr()->orX()
            ->add( 'm.attack = ?1' )
            ->add( 'm.defense = ?2' )
        );
        if( $ordered )
            $qb->orderBy( 'm.date ', 'DESC');
        $qb->setParameter( '1', $id );
        $qb->setParameter( '2', $id );
        return $qb->getQuery()->getResult();
    }

    function findByDate( \DateTime $time ) {
        $qb = $this->createQueryBuilder( 'm' );
        $qb->andWhere(
            $qb->expr()->andX()
                ->add( 'm.date >= ?1' )
                ->add( 'm.date < ?2' )
        );
        $qb->setParameter( '1', $time->format( 'Y-m-d' ) );
        $qb->setParameter( '2', $time->add( new \DateInterval( 'P1M' ) )->format( 'Y-m-d' ) );
        return $qb->getQuery()->getResult();
    }
}
