<?php

namespace MatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MatchResult
 *
 * @ORM\Table(name="match_result")
 * @ORM\Entity(repositoryClass="MatchBundle\Repository\MatchResultRepository")
 */
class MatchResult
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
     * @var \MatchBundle\Entity\Matchs
     *
     * @ORM\OneToOne(targetEntity="MatchBundle\Entity\Matchs", inversedBy="matchResult")
     * @ORM\JoinColumn(name="matchs", referencedColumnName="id")
     */
    private $match;

    /**
     * @var int
     *
     * @ORM\Column(name="nombre_tour", type="integer", nullable=true)
     */
    private $nombreTour;

    /**
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team")
     * @ORM\JoinColumn(name="winner", referencedColumnName="id")
     */
    private $winner;

    /**
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team")
     * @ORM\JoinColumn(name="first", referencedColumnName="id")
     */
    private $first;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="MatchBundle\Entity\MatchResultTeam", mappedBy="matchResult")
     */
    private $matchResultTeam;


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
     * Set nombreTour
     *
     * @param integer $nombreTour
     *
     * @return MatchResult
     */
    public function setNombreTour($nombreTour)
    {
        $this->nombreTour = $nombreTour;

        return $this;
    }

    /**
     * Get nombreTour
     *
     * @return int
     */
    public function getNombreTour()
    {
        return $this->nombreTour;
    }

    /**
     * Set winner
     *
     * @param \TeamBundle\Entity\Team $winner
     *
     * @return MatchResult
     */
    public function setWinner(\TeamBundle\Entity\Team $winner = null)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * Get winner
     *
     * @return \TeamBundle\Entity\Team
     */
    public function getWinner()
    {
        return $this->winner;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->matchResultTeam = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set match
     *
     * @param \MatchBundle\Entity\Matchs $match
     *
     * @return MatchResult
     */
    public function setMatch(\MatchBundle\Entity\Matchs $match = null)
    {
        $this->match = $match;

        return $this;
    }

    /**
     * Get match
     *
     * @return \MatchBundle\Entity\Matchs
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * Add matchResultTeam
     *
     * @param \MatchBundle\Entity\MatchResultTeam $matchResultTeam
     *
     * @return MatchResult
     */
    public function addMatchResultTeam(\MatchBundle\Entity\MatchResultTeam $matchResultTeam)
    {
        $this->matchResultTeam[] = $matchResultTeam;

        return $this;
    }

    /**
     * Remove matchResultTeam
     *
     * @param \MatchBundle\Entity\MatchResultTeam $matchResultTeam
     */
    public function removeMatchResultTeam(\MatchBundle\Entity\MatchResultTeam $matchResultTeam)
    {
        $this->matchResultTeam->removeElement($matchResultTeam);
    }

    /**
     * Get matchResultTeam
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMatchResultTeam()
    {
        return $this->matchResultTeam;
    }

    /**
     * Set first
     *
     * @param \TeamBundle\Entity\Team $first
     *
     * @return MatchResult
     */
    public function setFirst(\TeamBundle\Entity\Team $first = null)
    {
        $this->first = $first;

        return $this;
    }

    /**
     * Get first
     *
     * @return \TeamBundle\Entity\Team
     */
    public function getFirst()
    {
        return $this->first;
    }
}
