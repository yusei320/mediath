<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * @return Document[]
     */
    public function findDisponibles(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.disponible = :val')
            ->setParameter('val', true)
            ->orderBy('d.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Document[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.titre LIKE :query OR d.auteur LIKE :query OR d.type LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('d.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countDisponibles(): int
    {
        return $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->andWhere('d.disponible = :val')
            ->setParameter('val', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTotal(): int
    {
        return $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByType(): array
    {
        return $this->createQueryBuilder('d')
            ->select('d.type, COUNT(d.id) as count')
            ->groupBy('d.type')
            ->getQuery()
            ->getResult();
    }
}
