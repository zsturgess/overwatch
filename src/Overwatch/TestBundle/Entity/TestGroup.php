<?php

namespace Overwatch\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TestGroup
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class TestGroup
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
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;
    
    /**
     * @ORM\OneToMany(targetEntity="Test", mappedBy="group")
     */
    private $tests;
    
    /**
     * @ORM\OneToMany(targetEntity="TestGroup", mappedBy="parent")
     */
    private $children;
    
    /**
     * @ORM\ManyToOne(targetEntity="TestGroup", inversedBy="children")
     */
    private $parent;

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
     * Set description
     *
     * @param string $description
     * @return TestGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @ORM\PrePersist
     * @return TestGroup
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime;

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
     * Add children
     *
     * @param \Overwatch\TestBundle\Entity\TestGroup $children
     * @return TestGroup
     */
    public function addChild(\Overwatch\TestBundle\Entity\TestGroup $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Overwatch\TestBundle\Entity\TestGroup $children
     */
    public function removeChild(\Overwatch\TestBundle\Entity\TestGroup $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Overwatch\TestBundle\Entity\TestGroup $parent
     * @return TestGroup
     */
    public function setParent(\Overwatch\TestBundle\Entity\TestGroup $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Overwatch\TestBundle\Entity\TestGroup 
     */
    public function getParent()
    {
        return $this->parent;
    }
}
