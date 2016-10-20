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
        $res = array( 'pointsSuisse' => 0, 'pointsGoulta' => 0 );

        if( $this->getMatchResult()->getWinner() == $team ) {
            $res[ 'pointsSuisse' ] = 3;

            $mort = $this->getMatchResult()->getWinner() == $this->getAttack() ? $this->getMatchResult()->getMatchResultTeam()[0]->getNombreMort() : $this->getMatchResult()->getMatchResultTeam()[1]->getNombreMort();
            switch( $mort ) {
                case '0':
                    $res[ 'pointsGoulta' ] = 60;
                    break;
                case '1':
                    $res[ 'pointsGoulta' ] = 50;
                    break;
                case '2':
                    $res[ 'pointsGoulta' ] = 45;
                    break;
                case '3':
                    $res[ 'pointsGoulta' ] = 40;
                    break;
            }
        } else {
            $res[ 'pointsSuisse' ] = 0;

            $mort = $this->getMatchResult()->getWinner() == $this->getAttack() ? $this->getMatchResult()->getMatchResultTeam()[0]->getNombreMort() : $this->getMatchResult()->getMatchResultTeam()[1]->getNombreMort();
            switch( $mort ) {
                case '0':
                    $res['pointsGoulta'] = 5;
                    break;
                case '1':
                    $res['pointsGoulta'] = 15;
                    break;
                case '2':
                    $res['pointsGoulta'] = 20;
                    break;
                case '3':
                    $res['pointsGoulta'] = 25;
                    break;
            }
        }

        return $res;
    }
}
