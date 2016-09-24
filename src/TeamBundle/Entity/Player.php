<?php

namespace TeamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Player
 *
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="TeamBundle\Repository\PlayerRepository")
 */
class Player
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
     * @var string
     *
     * @ORM\Column(name="pseudo", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Le nom du joueur ne peut pas être vide")
     */
    private $pseudo;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer")
     * @Assert\NotBlank(message="Le niveau du joueur ne peut pas être vide")
     */
    private $level;

    /**
     * @var \TeamBundle\Entity\Classe
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Classe")
     * @ORM\JoinColumn(name="classe", referencedColumnName="id")
     * @Assert\NotNull(message="La classe du joueur ne peut pas être vide")
     */
    private $class;

    /**
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team", inversedBy="players")
     * @ORM\JoinColumn(name="team", referencedColumnName="id")
     */
    private $team;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isRemplacant", type="boolean")
     */
    private $isRemplacant;

    /**
     * @var \TeamBundle\Entity\Player
     *
     * @ORM\OneToOne(targetEntity="TeamBundle\Entity\Player")
     * @ORM\JoinColumn(name="remplacant", referencedColumnName="id", nullable=true)
     */
    private $remplacant;


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
     * Set pseudo
     *
     * @param string $pseudo
     *
     * @return Player
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Get pseudo
     *
     * @return string
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return Player
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set class
     *
     * @param integer $class
     *
     * @return Player
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return integer
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set team
     *
     * @param integer $team
     *
     * @return Player
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return integer
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set isRemplacant
     *
     * @param boolean $isRemplacant
     *
     * @return Player
     */
    public function setIsRemplacant($isRemplacant)
    {
        $this->isRemplacant = $isRemplacant;

        return $this;
    }

    /**
     * Get isRemplacant
     *
     * @return boolean
     */
    public function getIsRemplacant()
    {
        return $this->isRemplacant;
    }

    /**
     * Set remplacant
     *
     * @param \TeamBundle\Entity\Player $remplacant
     *
     * @return Player
     */
    public function setRemplacant(\TeamBundle\Entity\Player $remplacant = null)
    {
        $this->remplacant = $remplacant;

        return $this;
    }

    /**
     * Get remplacant
     *
     * @return \TeamBundle\Entity\Player
     */
    public function getRemplacant()
    {
        return $this->remplacant;
    }
}
