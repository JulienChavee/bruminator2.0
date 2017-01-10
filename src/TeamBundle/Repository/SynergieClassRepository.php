<?php

namespace TeamBundle\Repository;

use TeamBundle\Entity\SynergieClass;

/**
 * SynergieClassRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SynergieClassRepository extends \Doctrine\ORM\EntityRepository
{
    public function getSynergie( $class1, $class2 ) {
        $qb = $this->createQueryBuilder( 's' );

        $qb->select( 's.points' )
            ->where( $qb->expr()->andX()
                ->add( $qb->expr()->orX()
                    ->add( 's.class1 = :class1' )
                    ->add( 's.class2 = :class1' )
                )
                ->add( $qb->expr()->orX()
                    ->add( 's.class1 = :class2' )
                    ->add( 's.class2 = :class2' )
                )
            )
            ->setParameter( 'class1', $class1 )
            ->setParameter( 'class2', $class2 );

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function editSynergie( $class1, $class2, $points ) {
        $qb = $this->createQueryBuilder( 's' );

        $qb->update()
            ->set( 's.points', ':points' )
            ->where( $qb->expr()->andX()
                ->add( $qb->expr()->orX()
                    ->add( 's.class1 = :class1' )
                    ->add( 's.class2 = :class1' )
                )
                ->add( $qb->expr()->orX()
                    ->add( 's.class1 = :class2' )
                    ->add( 's.class2 = :class2' )
                )
            )
            ->setParameter( 'class1', $class1 )
            ->setParameter( 'class2', $class2 )
            ->setParameter( ':points', $points );

        if( !$qb->getQuery()->execute() ) {
            $synergie = new SynergieClass();
            $synergie->setClass1( $this->getEntityManager()->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $class1 ) ) );
            $synergie->setClass2( $this->getEntityManager()->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $class2 ) ) );
            $synergie->setPoints( $points );

            $this->getEntityManager()->persist( $synergie );
            $this->getEntityManager()->flush();
        }

        return true;
    }

    public function getClass4( $class1, $class2, $class3 ) {
        $synergie = 0;

        $classRepository = $this->getEntityManager()->getRepository( 'TeamBundle:Classe' );

        $synergie += $classRepository->getClassPoints( $class1 );
        $synergie += $classRepository->getClassPoints( $class2 );
        $synergie += $classRepository->getClassPoints( $class3 );

        $synergie += $this->getSynergie( $class1, $class2 );
        $synergie += $this->getSynergie( $class1, $class3 );
        $synergie += $this->getSynergie( $class2, $class3 );

        if( $synergie < $this->getEntityManager()->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'synergie_max' ) ) ) {
            $res = array();

            foreach( $classRepository->getClassesNotIn( array( $class1, $class2, $class3 ) ) as $k => $v ) {
                $temp = 0;
                $temp += $this->getSynergie( $v->getId(), $class1 );
                $temp += $this->getSynergie( $v->getId(), $class2 );
                $temp += $this->getSynergie( $v->getId(), $class3 );

                if( $synergie + $v->getPoints() + $temp <= $this->getEntityManager()->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'synergie_max' ) ) )
                    $res[] = $v->getName();
            }

            if(count($res) > 0)
                return $res;
            else
                return false;
        } else {
            return false;
        }
    }
}
