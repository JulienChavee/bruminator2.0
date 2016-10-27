<?php

namespace MatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MatchResultTeam
 *
 * @ORM\Table(name="match_result_team")
 * @ORM\Entity(repositoryClass="MatchBundle\Repository\MatchResultTeamRepository")
 */
class MatchResultTeam
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
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team")
     * @ORM\JoinColumn(name="team", referencedColumnName="id")
     */
    private $team;

    /**
     * @var \MatchBundle\Entity\MatchResult
     *
     * @ORM\ManyToOne(targetEntity="MatchBundle\Entity\MatchResult", inversedBy="matchResultTeam")
     * @ORM\JoinColumn(name="match_result", referencedColumnName="id")
     */
    private $matchResult;

    /**
     * @var int
     *
     * @ORM\Column(name="nombre_mort", type="integer", nullable=true)
     */
    private $nombreMort;

    /**
     * @var array
     *
     * @ORM\Column(name="initiative", type="array", nullable=true)
     */
    private $initiative;

    /**
     * @var int
     *
     * @ORM\Column(name="retard", type="integer")
     */
    private $retard;

    /**
     * @var boolean
     *
     * @ORM\Column(name="forfait", type="boolean")
     */
    private $forfait;

    /**
     * @var array
     *
     * @ORM\Column(name="penalite", type="array", nullable=true)
     */
    private $penalite;


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
     * Set nombreMort
     *
     * @param integer $nombreMort
     *
     * @return MatchResultTeam
     */
    public function setNombreMort($nombreMort)
    {
        $this->nombreMort = $nombreMort;

        return $this;
    }

    /**
     * Get nombreMort
     *
     * @return int
     */
    public function getNombreMort()
    {
        return $this->nombreMort;
    }

    /**
     * Set initiative
     *
     * @param array $initiative
     *
     * @return MatchResultTeam
     */
    public function setInitiative($initiative)
    {
        $this->initiative = $initiative;

        return $this;
    }

    /**
     * Get initiative
     *
     * @return array
     */
    public function getInitiative()
    {
        return $this->initiative;
    }

    /**
     * Set retard
     *
     * @param integer $retard
     *
     * @return MatchResultTeam
     */
    public function setRetard($retard)
    {
        $this->retard = $retard;

        return $this;
    }

    /**
     * Get retard
     *
     * @return int
     */
    public function getRetard()
    {
        return $this->retard;
    }

    /**
     * Set team
     *
     * @param \TeamBundle\Entity\Team $team
     *
     * @return MatchResultTeam
     */
    public function setTeam(\TeamBundle\Entity\Team $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \TeamBundle\Entity\Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set matchResult
     *
     * @param \MatchBundle\Entity\MatchResult $matchResult
     *
     * @return MatchResultTeam
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

    /**
     * Set forfait
     *
     * @param boolean $forfait
     *
     * @return MatchResultTeam
     */
    public function setForfait($forfait)
    {
        $this->forfait = $forfait;

        return $this;
    }

    /**
     * Get forfait
     *
     * @return boolean
     */
    public function getForfait()
    {
        return $this->forfait;
    }

    /**
     * Set penalite
     *
     * @param array $penalite
     *
     * @return MatchResultTeam
     */
    public function setPenalite($penalite)
    {
        $this->penalite = $penalite;

        return $this;
    }

    /**
     * Get penalite
     *
     * @return array
     */
    public function getPenalite()
    {
        return $this->penalite;
    }
}
