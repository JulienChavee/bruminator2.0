<?php

namespace TeamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SynergieClass
 *
 * @ORM\Table(name="synergie_class")
 * @ORM\Entity(repositoryClass="TeamBundle\Repository\SynergieClassRepository")
 */
class SynergieClass
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
     * @var int
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Classe")
     * @ORM\JoinColumn(name="class1", referencedColumnName="id")
     */
    private $class1;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Classe")
     * @ORM\JoinColumn(name="class2", referencedColumnName="id")
     */
    private $class2;

    /**
     * @var int
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points;


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
     * Set class1
     *
     * @param integer $class1
     *
     * @return SynergieClass
     */
    public function setClass1($class1)
    {
        $this->class1 = $class1;

        return $this;
    }

    /**
     * Get class1
     *
     * @return integer
     */
    public function getClass1()
    {
        return $this->class1;
    }

    /**
     * Set class2
     *
     * @param integer $class2
     *
     * @return SynergieClass
     */
    public function setClass2($class2)
    {
        $this->class2 = $class2;

        return $this;
    }

    /**
     * Get class2
     *
     * @return integer
     */
    public function getClass2()
    {
        return $this->class2;
    }

    /**
     * Set points
     *
     * @param integer $points
     *
     * @return SynergieClass
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }
}
