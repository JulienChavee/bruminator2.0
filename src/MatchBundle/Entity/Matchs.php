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
}
