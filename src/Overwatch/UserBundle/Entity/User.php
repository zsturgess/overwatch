<?php

namespace Overwatch\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Overwatch\ResultBundle\Entity\TestResult;
use Overwatch\UserBundle\Enum\AlertSetting;
use Symfony\Component\Security\Core\Util\SecureRandom;

/**
 * User
 * 
 * @ORM\Entity
 * @ORM\Table(name="User")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToMany(targetEntity="Overwatch\TestBundle\Entity\TestGroup", inversedBy="users")
     * @ORM\JoinTable(name="UsersTestGroups")
     */
    protected $groups;
    
    /**
     * @ORM\Column(type="string", length=40)
     */
    protected $apiKey = null;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $alertSetting = AlertSetting::CHANGE;
    
    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     */
    protected $telephoneNumber = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Serialize object to JSON
     */
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "email" => $this->getEmail(),
            "alertSetting" => $this->getAlertSetting(),
            "telephoneNumber" => $this->getTelephoneNumber(),
            "lastLogin" => $this->getLastLogin() ? $this->getLastLogin()->getTimestamp() : '',
            "locked" => $this->isLocked(),
            "roles" => $this->getRoles()
        ];
    }
    
    /**
     * Set Email (and username)
     * 
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->setUsername($email);
        
        return $this;
    }
    
    /**
     * Get Alert Setting for this user
     * 
     * @return integer $alertSetting
     */
    public function getAlertSetting()
    {
        return $this->alertSetting;
    }
    
    /**
     * Set this user's alert setting
     * 
     * @param integer $setting
     * @return User
     */
    public function setAlertSetting($setting)
    {
        AlertSetting::isValid($setting);
        $this->alertSetting = $setting;
        
        return $this;
    }
    
    public function getApiKey()
    {
        return $this->apiKey;
    }
    
    public function resetApiKey()
    {
        $random = new SecureRandom();
        $this->apiKey = sha1($random->nextBytes(10));
        
        return $this;
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function generateApiKey()
    {
        if (empty($this->getApiKey())) {
            $this->resetApiKey();
        }
        
        return $this;
    }
    
    /**
     * Get this user's telephone number
     * 
     * @return string
     */
    public function getTelephoneNumber()
    {
        return $this->telephoneNumber;
    }

    /**
     * Set user's telephone number
     * 
     * @param string $telephoneNumber
     * @return User
     */
    public function setTelephoneNumber($telephoneNumber)
    {
        $this->telephoneNumber = $telephoneNumber;
        
        return $this;
    }
    
    /**
     * Should this user be alerted for the passed TestResult?
     * 
     * @param TestResult $result
     * @return bool
     */
    public function shouldBeAlerted(TestResult $result)
    {
        $setting = $this->getAlertSetting();
        
        if ($this->isLocked() || $setting === AlertSetting::NONE) {
            return false;
        }
        
        if (!$this->hasGroup($result->getTest()->getGroup()->getName())) {
            return false;
        }
        
        if ($setting === AlertSetting::ALL) {
            return true;
        }
        
        if (
            $result->isUnsuccessful()
            && ($setting === AlertSetting::CHANGE_ALL)
        ) {
            return true;
        }
        
        if ($result->isAChange()) {
            if (in_array($setting, [AlertSetting::CHANGE, AlertSetting::CHANGE_ALL])) {
                return true;
            }
            
            if (
                $result->isUnsuccessful()
                && $setting === AlertSetting::CHANGE_BAD
            ) {
                return true;
            }
        }
        
        return false;
    }
}
