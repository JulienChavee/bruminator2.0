<?php

namespace TeamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Team
 *
 * @ORM\Table(name="team")
 * @ORM\Entity(repositoryClass="TeamBundle\Repository\TeamRepository")
 */
class Team
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Le nom de l'équipe ne peut pas être vide")
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inscriptionDate", type="datetime")
     */
    private $inscriptionDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="valid", type="boolean")
     */
    private $valid;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid;

    /**
     * @var string
     *
     * @ORM\Column(name="available", type="text")
     */
    private $available;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="TeamBundle\Entity\Player", mappedBy="team")
     */
    private $players;

    /**
     * @var array
     *
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\User", mappedBy="team")
     */
    private $managers;

    /**
     * @var bool
     *
     * @ORM\Column(name="registered", type="boolean", options={"default":false})
     */
    private $registered;


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
     * Set name
     *
     * @param string $name
     *
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set inscriptionDate
     *
     * @param \DateTime $inscriptionDate
     *
     * @return Team
     */
    public function setInscriptionDate($inscriptionDate)
    {
        $this->inscriptionDate = $inscriptionDate;

        return $this;
    }

    /**
     * Get inscriptionDate
     *
     * @return \DateTime
     */
    public function getInscriptionDate()
    {
        return $this->inscriptionDate;
    }

    /**
     * Set valid
     *
     * @param boolean $valid
     *
     * @return Team
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return bool
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return Team
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set available
     *
     * @param string $available
     *
     * @return Team
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get available
     *
     * @return string
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @return array
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param array $players
     */
    public function setPlayers($players)
    {
        $this->players = $players;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->players = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add player
     *
     * @param \TeamBundle\Entity\Player $player
     *
     * @return Team
     */
    public function addPlayer(\TeamBundle\Entity\Player $player)
    {
        $this->players[] = $player;

        return $this;
    }

    /**
     * Remove player
     *
     * @param \TeamBundle\Entity\Player $player
     */
    public function removePlayer(\TeamBundle\Entity\Player $player)
    {
        $this->players->removeElement($player);
    }

    /**
     * Add manager
     *
     * @param \UserBundle\Entity\User $manager
     *
     * @return Team
     */
    public function addManager(\UserBundle\Entity\User $manager)
    {
        $this->managers[] = $manager;

        return $this;
    }

    /**
     * Remove manager
     *
     * @param \UserBundle\Entity\User $manager
     */
    public function removeManager(\UserBundle\Entity\User $manager)
    {
        $this->managers->removeElement($manager);
    }

    /**
     * Get managers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getManagers()
    {
        return $this->managers;
    }

    /**
     * Set registered
     *
     * @param boolean $registered
     *
     * @return Team
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;

        return $this;
    }

    /**
     * Get registered
     *
     * @return boolean
     */
    public function getRegistered()
    {
        return $this->registered;
    }
}
