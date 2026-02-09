<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;

class DocumentService
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Recherche de documents (centraliser la logique)
     * 
     * @return Document[]
     */
    public function search(string $query): array
    {
        return $this->documentRepository->search($query);
    }

    /**
     * Obtenir les documents disponibles
     * 
     * @return Document[]
     */
    public function getDisponibles(?string $type = null): array
    {
        $criteria = ['disponible' => true];
        if ($type) {
            $criteria['type'] = $type;
        }

        return $this->documentRepository->findBy($criteria);
    }

    /**
     * Marquer un document comme disponible/indisponible
     */
    public function setDisponibilite(Document $document, bool $disponible): void
    {
        $document->setDisponible($disponible);
        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }

    /**
     * Obtenir les statistiques des documents
     */
    public function getStatistiques(): array
    {
        return [
            'total' => $this->documentRepository->count([]),
            'disponibles' => $this->documentRepository->countDisponibles(),
            'empruntes' => $this->documentRepository->count(['disponible' => false]),
            'par_type' => $this->documentRepository->countByType(),
        ];
    }
}
