<?php

namespace App\Repository;

use App\Entity\DemandeEmprunt;
use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeEmprunt>
 */
class DemandeEmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeEmprunt::class);
    }

    /**
     * Nombre de demandes en attente (pour badge)
     */
    public function countEnAttente(): int
    {
        return $this->count(['statut' => DemandeEmprunt::STATUT_EN_ATTENTE]);
    }

    /**
     * Demandes en attente pour un bibliothécaire spécifique
     */
    public function findEnAttenteForBibliothecaire(User $bibliothecaire): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.bibliothecaire = :biblio')
            ->andWhere('d.statut = :statut')
            ->setParameter('biblio', $bibliothecaire)
            ->setParameter('statut', DemandeEmprunt::STATUT_EN_ATTENTE)
            ->orderBy('d.dateDemande', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les demandes d'un adhérent
     */
    public function findByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.document', 'doc')
            ->leftJoin('d.bibliothecaire', 'biblio')
            ->addSelect('doc', 'biblio')
            ->where('d.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('d.dateDemande', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si l'adhérent a déjà une demande EN_ATTENTE pour ce document
     */
    public function hasDemandeEnAttenteForDocument(Adherent $adherent, Document $document): bool
    {
        return $this->count([
            'adherent' => $adherent,
            'document' => $document,
            'statut' => DemandeEmprunt::STATUT_EN_ATTENTE
        ]) > 0;
    }
}
