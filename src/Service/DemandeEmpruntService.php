<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DemandeEmprunt;
use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\User;
use App\Repository\DemandeEmpruntRepository;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;

class DemandeEmpruntService
{
    public function __construct(
        private readonly DemandeEmpruntRepository $demandeRepository,
        private readonly EmpruntRepository $empruntRepository,
        private readonly EmpruntService $empruntService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(int $id): ?DemandeEmprunt
    {
        return $this->demandeRepository->find($id);
    }

    /**
     * Vérifie si un adhérent peut faire une demande pour un document
     */
    public function canDemanderEmprunt(Adherent $adherent, Document $document): array
    {
        $errors = [];

        // 1. Document disponible ?
        if (!$document->isDisponible()) {
            $errors[] = "Ce document n'est plus disponible.";
        }

        // 2. Adhérent a des retards ?
        if ($this->empruntRepository->hasRetards($adherent)) {
            $errors[] = "Vous avez des emprunts en retard. Veuillez les régulariser avant de faire une nouvelle demande.";
        }

        // 3. Demande déjà en attente ?
        if ($this->demandeRepository->hasDemandeEnAttenteForDocument($adherent, $document)) {
            $errors[] = "Vous avez déjà une demande en attente pour ce document.";
        }

        return [
            'can_request' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Créer une nouvelle demande d'emprunt
     */
    public function creerDemande(
        Adherent $adherent,
        Document $document,
        User $bibliothecaire,
        ?\DateTimeInterface $dateEmpruntSouhaitee = null,
        ?int $dureeSouhaiteeJours = 14,
        ?string $message = null
    ): DemandeEmprunt {
        $demande = new DemandeEmprunt();
        $demande->setAdherent($adherent);
        $demande->setDocument($document);
        $demande->setBibliothecaire($bibliothecaire);
        $demande->setDateDemande(new \DateTime());
        $demande->setDateEmpruntSouhaitee($dateEmpruntSouhaitee ?? new \DateTime());
        $demande->setDureeSouhaiteeJours($dureeSouhaiteeJours);
        $demande->setMessageAdherent($message);
        $demande->setStatut(DemandeEmprunt::STATUT_EN_ATTENTE);

        $this->entityManager->persist($demande);
        $this->entityManager->flush();

        return $demande;
    }

    /**
     * Accepter une demande et créer l'emprunt automatiquement
     */
    public function accepterDemande(DemandeEmprunt $demande): void
    {
        // Vérifications finales
        if ($demande->getStatut() !== DemandeEmprunt::STATUT_EN_ATTENTE) {
            throw new \LogicException('Cette demande a déjà été traitée.');
        }

        if (!$demande->getDocument()->isDisponible()) {
            throw new \LogicException('Le document n\'est plus disponible.');
        }

        // Créer l'emprunt via le service
        $emprunt = $this->empruntService->creerEmprunt(
            $demande->getAdherent(),
            $demande->getDocument(),
            $demande->getDateEmpruntSouhaitee() ?? new \DateTime(),
            $demande->getDureeSouhaiteeJours() ?? 14
        );

        // Mettre à jour la demande
        $demande->setStatut(DemandeEmprunt::STATUT_ACCEPTEE);
        $demande->setDateTraitement(new \DateTime());
        $demande->setEmpruntCree($emprunt);

        $this->entityManager->persist($demande);
        $this->entityManager->flush();
    }

    /**
     * Refuser une demande avec motif
     */
    public function refuserDemande(DemandeEmprunt $demande, string $motifRefus): void
    {
        if ($demande->getStatut() !== DemandeEmprunt::STATUT_EN_ATTENTE) {
            throw new \LogicException('Cette demande a déjà été traitée.');
        }

        if (empty(trim($motifRefus))) {
            throw new \InvalidArgumentException('Le motif de refus est obligatoire.');
        }

        $demande->setStatut(DemandeEmprunt::STATUT_REFUSEE);
        $demande->setMotifRefus($motifRefus);
        $demande->setDateTraitement(new \DateTime());

        $this->entityManager->persist($demande);
        $this->entityManager->flush();
    }
}
