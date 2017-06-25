<?php

namespace MatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Matchs
 *
 * @ORM\Table(name="matchs")
 * @ORM\Entity(repositoryClass="MatchBundle\Repository\MatchsRepository")
 */
class Matchs
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var \UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="arbitre", referencedColumnName="id")
     */
    private $arbitre;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team")
     * @ORM\JoinColumn(name="attack", referencedColumnName="id")
     */
    private $attack;

    /**
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team")
     * @ORM\JoinColumn(name="defense", referencedColumnName="id")
     */
    private $defense;

    /**
     * @var \MatchBundle\Entity\MatchResult
     *
     * @ORM\OneToOne(targetEntity="MatchBundle\Entity\MatchResult", mappedBy="match")
     */
    private $matchResult;

    /**
     * @var \MatchBundle\Entity\Map
     *
     * @ORM\ManyToOne(targetEntity="MatchBundle\Entity\Map")
     * @ORM\JoinColumn(name="map", referencedColumnName="id")
     */
    private $map;

    /**
     * @var \MainBundle\Entity\Edition
     *
     * @ORM\ManyToOne(targetEntity="MainBundle\Entity\Edition")
     * @ORM\JoinColumn(name="edition", referencedColumnName="id")
     */
    private $edition;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Matchs
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Matchs
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set arbitre
     *
     * @param \UserBundle\Entity\User $arbitre
     *
     * @return Matchs
     */
    public function setArbitre(\UserBundle\Entity\User $arbitre = null)
    {
        $this->arbitre = $arbitre;

        return $this;
    }

    /**
     * Get arbitre
     *
     * @return \UserBundle\Entity\User
     */
    public function getArbitre()
    {
        return $this->arbitre;
    }

    /**
     * Set attack
     *
     * @param \TeamBundle\Entity\Team $attack
     *
     * @return Matchs
     */
    public function setAttack(\TeamBundle\Entity\Team $attack = null)
    {
        $this->attack = $attack;

        return $this;
    }

    /**
     * Get attack
     *
     * @return \TeamBundle\Entity\Team
     */
    public function getAttack()
    {
        return $this->attack;
    }

    /**
     * Set defense
     *
     * @param \TeamBundle\Entity\Team $defense
     *
     * @return Matchs
     */
    public function setDefense(\TeamBundle\Entity\Team $defense = null)
    {
        $this->defense = $defense;

        return $this;
    }

    /**
     * Get defense
     *
     * @return \TeamBundle\Entity\Team
     */
    public function getDefense()
    {
        return $this->defense;
    }

    /**
     * Set map
     *
     * @param \MatchBundle\Entity\Map $map
     *
     * @return Matchs
     */
    public function setMap(\MatchBundle\Entity\Map $map = null)
    {
        $this->map = $map;

        return $this;
    }

    /**
     * Get map
     *
     * @return \MatchBundle\Entity\Map
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Set matchResult
     *
     * @param \MatchBundle\Entity\MatchResult $matchResult
     *
     * @return Matchs
     */
    public function setMatchResult(\MatchBundle\Entity\MatchResult $matchResult = null)
    {
        $this->matchResult = $matchResult;

        return $this;
    }

    /**
     * Get matchResult
     *
     * @return \MatchBundle\Entity\MatchResult
     */
    public function getMatchResult()
    {
        return $this->matchResult;
    }

    public function getPoints( \TeamBundle\Entity\Team $team ) {
        $res = array( 'pointsSuisse' => 0, 'pointsGoulta' => 0, 'detail' => array() );

        $forfait = $this->getMatchResult()->getMatchResultTeam()[0]->getForfait() || $this->getMatchResult()->getMatchResultTeam()[1]->getForfait();

        if( $this->getMatchResult()->getWinner() == $team ) {
            $res[ 'pointsSuisse' ] = 3;
            $res[ 'details' ][ 'pointsSuisse' ][] = array( 'nb' => 3, 'explication' => 'Victoire' );

            switch($this->getEdition()->getId())
            {
                case '16':
                    if( $forfait ){
                        $res['pointsGoulta'] = 50;
                        $res['details']['pointsGoulta'][] = array('nb' => 50, 'explication' => 'Victoire par forfait');
                    } else {
                        $mort = $this->getMatchResult()->getWinner() == $this->getAttack() ? $this->getMatchResult()->getMatchResultTeam()[0]->getNombreMort() : $this->getMatchResult()->getMatchResultTeam()[1]->getNombreMort();
                        switch ($mort) {
                            case '0':
                                $res['pointsGoulta'] = 50;
                                $res['details']['pointsGoulta'][] = array('nb' => 50, 'explication' => 'Victoire parfaite');
                                break;
                            case '1':
                                $res['pointsGoulta'] = 45;
                                $res['details']['pointsGoulta'][] = array('nb' => 45, 'explication' => 'Victoire à deux');
                                break;
                            case '2':
                                $res['pointsGoulta'] = 40;
                                $res['details']['pointsGoulta'][] = array('nb' => 40, 'explication' => 'Victoire sur le fil');
                                break;
                        }

                        if ($this->getMatchResult()->getNombreTour() < 9) {
                            $res['pointsGoulta'] += 20;
                            $res['details']['pointsGoulta'][] = array('nb' => 20, 'explication' => 'Victoire écrasante');
                        }
                    }
                    break;
                default:
                    if( $forfait ){
                        $res['pointsGoulta'] = 60;
                        $res['details']['pointsGoulta'][] = array('nb' => 60, 'explication' => 'Victoire par forfait');
                    } else {
                        $mort = $this->getMatchResult()->getWinner() == $this->getAttack() ? $this->getMatchResult()->getMatchResultTeam()[0]->getNombreMort() : $this->getMatchResult()->getMatchResultTeam()[1]->getNombreMort();
                        switch ($mort) {
                            case '0':
                                $res['pointsGoulta'] = 60;
                                $res['details']['pointsGoulta'][] = array('nb' => 60, 'explication' => 'Victoire parfaite');
                                break;
                            case '1':
                                $res['pointsGoulta'] = 50;
                                $res['details']['pointsGoulta'][] = array('nb' => 50, 'explication' => 'Victoire à trois');
                                break;
                            case '2':
                                $res['pointsGoulta'] = 45;
                                $res['details']['pointsGoulta'][] = array('nb' => 45, 'explication' => 'Victoire à deux');
                                break;
                            case '3':
                                $res['pointsGoulta'] = 40;
                                $res['details']['pointsGoulta'][] = array('nb' => 40, 'explication' => 'Victoire sur le fil');
                                break;
                        }

                        if ($this->getMatchResult()->getNombreTour() < 9) {
                            $res['pointsGoulta'] += 20;
                            $res['details']['pointsGoulta'][] = array('nb' => 20, 'explication' => 'Victoire écrasante');
                        }
                    }
            }

        } else {
            $res[ 'pointsSuisse' ] = 0;
            $res[ 'details' ][ 'pointsSuisse' ][] = array( 'nb' => 0, 'explication' => 'Défaite' );

            switch($this->getEdition()->getId())
            {
                case '16':
                    if( $forfait ) {
                        $res['pointsGoulta'] = -10;
                        $res['details']['pointsGoulta'][] = array('nb' => -10, 'explication' => 'Forfait');
                    } else {
                        $mort = $this->getMatchResult()->getWinner() == $this->getAttack() ? $this->getMatchResult()->getMatchResultTeam()[0]->getNombreMort() : $this->getMatchResult()->getMatchResultTeam()[1]->getNombreMort();
                        switch ($mort) {
                            case '0':
                                $res['pointsGoulta'] = 5;
                                $res['details']['pointsGoulta'][] = array('nb' => 5, 'explication' => 'Victoire parfaite (adverse)');
                                break;
                            case '1':
                                $res['pointsGoulta'] = 15;
                                $res['details']['pointsGoulta'][] = array('nb' => 15, 'explication' => 'Victoire à deux (adverse)');
                                break;
                            case '2':
                                $res['pointsGoulta'] = 20;
                                $res['details']['pointsGoulta'][] = array('nb' => 20, 'explication' => 'Victoire sur le fil (adverse)');
                                break;
                        }
                    }
                    break;
                default:
                    if( $forfait ) {
                        $res['pointsGoulta'] = -10;
                        $res['details']['pointsGoulta'][] = array('nb' => -10, 'explication' => 'Forfait');
                    } else {
                        $mort = $this->getMatchResult()->getWinner() == $this->getAttack() ? $this->getMatchResult()->getMatchResultTeam()[0]->getNombreMort() : $this->getMatchResult()->getMatchResultTeam()[1]->getNombreMort();
                        switch ($mort) {
                            case '0':
                                $res['pointsGoulta'] = 5;
                                $res['details']['pointsGoulta'][] = array('nb' => 5, 'explication' => 'Victoire parfaite (adverse)');
                                break;
                            case '1':
                                $res['pointsGoulta'] = 15;
                                $res['details']['pointsGoulta'][] = array('nb' => 15, 'explication' => 'Victoire à trois (adverse)');
                                break;
                            case '2':
                                $res['pointsGoulta'] = 20;
                                $res['details']['pointsGoulta'][] = array('nb' => 20, 'explication' => 'Victoire à deux (adverse)');
                                break;
                            case '3':
                                $res['pointsGoulta'] = 25;
                                $res['details']['pointsGoulta'][] = array('nb' => 25, 'explication' => 'Victoire sur le fil (adverse)');
                                break;
                        }
                    }
            }

        }

        if( $team == $this->getAttack() )
            $matchResult = $this->getMatchResult()->getMatchResultTeam()[0];
        else
            $matchResult = $this->getMatchResult()->getMatchResultTeam()[1];

        if( $matchResult->getRetard() == 30 ) {
            $res['pointsGoulta'] -= 20;
            $res[ 'details' ][ 'pointsGoulta' ][] = array( 'nb' => -20, 'explication' => 'Retard (30 minutes)' );
        } else if( $matchResult->getRetard() >=25 ) {
            $res['pointsGoulta'] -= 15;
            $res[ 'details' ][ 'pointsGoulta' ][] = array( 'nb' => -15, 'explication' => 'Retard (25 minutes)' );
        } else if( $matchResult->getRetard() >= 20) {
            $res['pointsGoulta'] -= 10;
            $res[ 'details' ][ 'pointsGoulta' ][] = array( 'nb' => -10, 'explication' => 'Retard (20 minutes)' );
        }else if( $matchResult->getRetard() >= 15 ) {
            $res['pointsGoulta'] -= 5;
            $res[ 'details' ][ 'pointsGoulta' ][] = array( 'nb' => -5, 'explication' => 'Retard (15 minutes)' );
        }

        if( $penalite = $matchResult->getPenalite() ) {
            if( $penalite[ 'suisse' ] > 0 ) {
                $res[ 'pointsSuisse' ] -= $penalite[ 'suisse' ];
                $res[ 'details' ][ 'pointsSuisse' ][] = array( 'nb' => -1 * $penalite[ 'suisse' ], 'explication' => $penalite[ 'raison' ] );
            }

            if( $penalite[ 'goulta' ] > 0 ) {
                $res[ 'pointsGoulta' ] -= $penalite[ 'goulta'];
                $res[ 'details' ][ 'pointsGoulta' ][] = array( 'nb' => -1 * $penalite[ 'goulta' ], 'explication' => $penalite[ 'raison' ] );
            }
        }

        return $res;
    }

    /**
     * Set edition
     *
     * @param \MainBundle\Entity\Edition $edition
     *
     * @return Matchs
     */
    public function setEdition(\MainBundle\Entity\Edition $edition = null)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Get edition
     *
     * @return \MainBundle\Entity\Edition
     */
    public function getEdition()
    {
        return $this->edition;
    }
}
