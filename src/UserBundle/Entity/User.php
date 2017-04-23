<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser implements EquatableInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \TeamBundle\Entity\Team
     *
     * @ORM\ManyToOne(targetEntity="TeamBundle\Entity\Team", inversedBy="managers")
     * @ORM\JoinColumn(name="team", referencedColumnName="id")
     */
    private $team;

    /**
     * @var string
     *
     * @ORM\Column(name="arbitre_disponibilite", type="text")
     */
    private $arbitreDisponibilite;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    public function isEqualTo(UserInterface $user)
    {
        if ($user instanceof User) {
            // Check that the roles are the same, in any order
            $isEqual = count($this->getRoles()) == count($user->getRoles());
            if ($isEqual) {
                foreach($this->getRoles() as $role) {
                    $isEqual = $isEqual && in_array($role, $user->getRoles());
                }
            }

            return $isEqual;
        }
        return false;
    }


    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @return string
     */
    public function getArbitreDisponibilite()
    {
        return $this->arbitreDisponibilite;
    }

    /**
     * @param string $arbitreDisponibilite
     */
    public function setArbitreDisponibilite($arbitreDisponibilite)
    {
        $this->arbitreDisponibilite = $arbitreDisponibilite;
    }


}