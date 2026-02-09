<?php

namespace App\Security\Voter;

use App\Entity\DemandeEmprunt;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DemandeEmpruntVoter extends Voter
{
    public const VIEW = 'DEMANDE_VIEW';
    public const PROCESS = 'DEMANDE_PROCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::PROCESS])
            && $subject instanceof DemandeEmprunt;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var DemandeEmprunt $demande */
        $demande = $subject;

        if (in_array(User::ROLE_ADMIN, $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                // Adherent can view his own requests
                if ($user->getUserIdentifier() === $demande->getAdherent()->getEmail()) {
                    return true;
                }
                // Librarian can view all (or assigned ones)
                if ($user->isBibliothecaire()) {
                    return true;
                }
                break;

            case self::PROCESS:
                // Only assigned librarian can process (or admin)
                if ($user === $demande->getBibliothecaire()) {
                    return true;
                }
                break;
        }

        return false;
    }
}
