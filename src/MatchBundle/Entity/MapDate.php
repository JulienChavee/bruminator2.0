<?php

namespace MatchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MapDate
 *
 * @ORM\Table(name="map_date")
 * @ORM\Entity(repositoryClass="MatchBundle\Repository\MapDateRepository")
 */
class MapDate
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
     * @var \MatchBundle\Entity\Map
     *
     * @ORM\ManyToOne(targetEntity="MatchBundle\Entity\Map")
     * @ORM\JoinColumn(name="map", referencedColumnName="id")
     */
    private $map;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Date", type="date")
     */
    private $date;

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
     * @return MapDate
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
     * Set map
     *
     * @param \MatchBundle\Entity\Map $map
     *
     * @return MapDate
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
     * Set edition
     *
     * @param \MainBundle\Entity\Edition $edition
     *
     * @return MapDate
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
