<?php

namespace App\Security\Voter;

use App\Entity\Emprunt;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EmpruntVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof Emprunt;
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

        /** @var Emprunt $emprunt */
        $emprunt = $subject;

        switch ($attribute) {
            case self::VIEW:
                // Adherent can view their own loans
                // Check if the user email matches the adherent email
                return $user->getEmail() === $emprunt->getAdherent()->getEmail();
                
            case self::EDIT:
                // Adherents cannot edit loans
                return false;
        }

        return false;
    }
}
