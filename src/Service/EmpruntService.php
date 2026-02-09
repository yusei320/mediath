<?php

namespace App\Service;

use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\Emprunt;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class EmpruntService
{
    public function __construct(
        private EmpruntRepository $empruntRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function canEmprunter(Adherent $adherent, Document $document): bool
    {
        if (!$document->isDisponible()) {
            return false;
        }

        if (!$adherent->isActif()) {
            return false;
        }

        if ($this->empruntRepository->hasRetards($adherent)) {
            return false;
        }

        return true;
    }

    public function creerEmprunt(Adherent $adherent, Document $document): Emprunt
    {
        if (!$this->canEmprunter($adherent, $document)) {
            throw new BadRequestException("L'adhérent ne peut pas emprunter ce document (Retards, Compte inactif ou Document indisponible).");
        }

        $emprunt = new Emprunt();
        $emprunt->setAdherent($adherent);
        $emprunt->setDocument($document);
        $emprunt->setDateEmprunt(new \DateTime());
        
        $dateRetour = new \DateTime();
        $dateRetour->modify('+3 weeks');
        $emprunt->setDateRetourPrevue($dateRetour);
        
        $emprunt->setStatut(Emprunt::STATUT_EN_COURS);

        // Availability is handled by EmpruntSubscriber, but we can double check logic here
        // $document->setDisponible(false); 

        $this->em->persist($emprunt);
        $this->em->flush();

        return $emprunt;
    }

    public function retournerEmprunt(Emprunt $emprunt): void
    {
        $now = new \DateTime();
        $emprunt->setDateRetourEffective($now);

        if ($now > $emprunt->getDateRetourPrevue()) {
            $emprunt->setStatut(Emprunt::STATUT_RETARD);
        } else {
            $emprunt->setStatut(Emprunt::STATUT_TERMINE);
        }

        // Availability is handled by EmpruntSubscriber
        // $emprunt->getDocument()->setDisponible(true);

        $this->em->flush();
    }
}
