<?php

namespace Overwatch\ResultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Overwatch\ResultBundle\Enum\ResultStatus;

/**
 * TestResult
 *
 * @ORM\Table()
 * @ORM\Entity(readOnly=true,repositoryClass="Overwatch\ResultBundle\Entity\TestResultRepository")
 * @ORM\HasLifecycleCallbacks
 */
class TestResult implements \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="Overwatch\TestBundle\Entity\Test", inversedBy="results")
     */
    private $test;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=15)
     */
    private $status;
    
    /**
     * @var string
     *
     * @ORM\Column(name="info", type="string", length=100)
     */
    private $info;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;
    
    /**
     * Serialise object to JSON
     */
    public function jsonSerialize() {
        return [
            "id" => $this->getId(),
            "status" => $this->getStatus(),
            "info" => $this->getInfo(),
            "createdAt" => $this->getCreatedAt()->getTimestamp()
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
     * Set result
     *
     * @param string $result
     * @return TestResult
     */
    public function setStatus($status)
    {
        ResultStatus::isValid($status);
        $this->status = $status;

        return $this;
    }

    /**
     * Get result
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createdAt
     *
     * @ORM\PrePersist
     * @return TestResult
     */
    public function setCreatedAt()
    {
        if ($this->createdAt === NULL) {
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
     * Set test
     *
     * @param \Overwatch\TestBundle\Entity\Test $test
     * @return TestResult
     */
    public function setTest(\Overwatch\TestBundle\Entity\Test $test = null)
    {
        $this->test = $test;

        return $this;
    }

    /**
     * Get test
     *
     * @return \Overwatch\TestBundle\Entity\Test 
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return TestResult
     */
    public function setInfo($info)
    {
        if ($info instanceof \Exception) {
            $info = $info->getMessage();
        }
        
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }
}
