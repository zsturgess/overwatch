<?php

namespace Overwatch\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;

/**
 * TestGroup
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class TestGroup implements GroupInterface, \JsonSerializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     */
    private $name;
    
    /**
     * @ORM\OneToMany(targetEntity="Test", mappedBy="group")
     */
    private $tests;
    
    /**
     * @ORM\ManyToMany(targetEntity="Overwatch\UserBundle\Entity\User", mappedBy="groups")
     */
    private $users;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tests = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Serialise object to JSON
     */
    public function jsonSerialize()
    {
        return [
            'id'        => $this->getId(),
            'name'      => $this->getName(),
            'tests'     => $this->getTests()->toArray(),
            'users'     => $this->getUsers()->toArray(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'updatedAt' => $this->getUpdatedAt()->getTimestamp()
        ];
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TestGroup
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
     * Set createdAt
     *
     * @ORM\PrePersist
     * @return TestGroup
     */
    public function setCreatedAt()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime;
        }
        
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @return TestGroup
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add tests
     *
     * @param \Overwatch\TestBundle\Entity\Test $tests
     * @return TestGroup
     */
    public function addTest(\Overwatch\TestBundle\Entity\Test $tests)
    {
        $this->tests[] = $tests;

        return $this;
    }

    /**
     * Remove tests
     *
     * @param \Overwatch\TestBundle\Entity\Test $tests
     */
    public function removeTest(\Overwatch\TestBundle\Entity\Test $tests)
    {
        $this->tests->removeElement($tests);
    }

    /**
     * Get tests
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTests()
    {
        return $this->tests;
    }

    /**
     * Add users
     *
     * @param \Overwatch\UserBundle\Entity\User $user
     * @return TestGroup
     */
    public function addUser(\Overwatch\UserBundle\Entity\User $user)
    {
        $user->addGroup($this);
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Overwatch\UserBundle\Entity\User $user
     */
    public function removeUser(\Overwatch\UserBundle\Entity\User $user)
    {
        $user->removeGroup($this);
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
    
    /**
     * FOSUserBundle needs groups to have roles. We don't.
     */
    public function getRoles()
    {
        return [];
    }
    public function setRoles(array $roles)
    {
        return $this;
    }
    public function addRole($role)
    {
        return $this;
    }
    public function removeRole($role)
    {
        return $this;
    }
    public function hasRole($role)
    {
        return false;
    }
}
