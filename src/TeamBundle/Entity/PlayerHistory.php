<?php

namespace TeamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlayerHistory
 *
 * @ORM\Table(name="player_history")
 * @ORM\Entity(repositoryClass="TeamBundle\Repository\PlayerHistoryRepository")
 */
class PlayerHistory
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
     * @var \TeamBundle\Entity\Player
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Player")
     * @ORM\JoinColumn(name="player", referencedColumnName="id")
     */
    private $player;

    /**
     * @var \TeamBundle\Entity\Classe
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Classe")
     * @ORM\JoinColumn(name="previousClasse", referencedColumnName="id")
     */
    private $previousClass;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * @return PlayerHistory
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
     * Set player
     *
     * @param \TeamBundle\Entity\Player $player
     *
     * @return PlayerHistory
     */
    public function setPlayer(\TeamBundle\Entity\Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \TeamBundle\Entity\Player
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Set previousClass
     *
     * @param \TeamBundle\Entity\Classe $previousClass
     *
     * @return PlayerHistory
     */
    public function setPreviousClass(\TeamBundle\Entity\Classe $previousClass = null)
    {
        $this->previousClass = $previousClass;

        return $this;
    }

    /**
     * Get previousClass
     *
     * @return \TeamBundle\Entity\Classe
     */
    public function getPreviousClass()
    {
        return $this->previousClass;
    }
}
