<?php

namespace Overwatch\TestBundle\Security;

use Overwatch\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * TestGroupVoter
 */
class TestGroupVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';
    
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
        ]);
    }
    
    public function supportsClass($class)
    {
        $supportedClass = 'Overwatch\TestBundle\Entity\TestGroup';
        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }
    
    public function vote(TokenInterface $token, $group, array $attributes)
    {
        // check if class of this object is supported by this voter
        if (!$this->supportsClass(get_class($group))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the given attribute is covered by this voter
        if (!$this->supportsAttribute($attributes[0])) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // get current logged in user
        $user = $token->getUser();
        
        //allow the token to have ROLE_SUPER_ADMIN before we check the user, for testing
        if (in_array(new Role('ROLE_SUPER_ADMIN'), $token->getRoles())) {
            return VoterInterface::ACCESS_GRANTED;
        }
        
        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof User) {
            return VoterInterface::ACCESS_DENIED;
        }

        switch ($attributes[0]) {
            case self::VIEW:
                if ($user->hasGroup($group->getName())) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

            case self::EDIT:
                if ($user->hasGroup($group->getName()) && $user->hasRole('ROLE_ADMIN')) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
