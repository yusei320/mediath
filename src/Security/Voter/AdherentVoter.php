<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdherentVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Adherent;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // Admin and Librarian can do anything
        if (in_array(User::ROLE_ADMIN, $user->getRoles()) || in_array(User::ROLE_BIBLIOTHECAIRE, $user->getRoles())) {
            return true;
        }

        /** @var Adherent $adherent */
        $adherent = $subject;

        switch ($attribute) {
            case self::VIEW:
                // Adherent can view their own profile
                return $user->getEmail() === $adherent->getEmail();
                
            case self::EDIT:
                // Adherent cannot edit their profile directly via this voter (usually handled by specific profile update form or admin)
                // For now, let's say NO, unless we implement profile editing.
                return false;
        }

        return false;
    }
}
