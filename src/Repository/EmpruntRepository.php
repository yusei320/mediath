<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emprunt>
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    /**
     * @return Emprunt[]
     */
    public function findEnRetard(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.dateRetourEffective IS NULL')
            ->andWhere('e.dateRetourPrevue < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.dateRetourPrevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Emprunt[]
     */
    public function findEnCours(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.dateRetourEffective IS NULL')
            ->orderBy('e.dateEmprunt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Emprunt[]
     */
    public function findByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('e.dateEmprunt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Emprunt[]
     */
    public function findEnCoursByAdherent(Adherent $adherent): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.adherent = :adherent')
            ->andWhere('e.dateRetourEffective IS NULL')
            ->setParameter('adherent', $adherent)
            ->orderBy('e.dateRetourPrevue', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function hasRetards(Adherent $adherent): bool
    {
        $count = $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->andWhere('e.adherent = :adherent')
            ->andWhere('e.dateRetourEffective IS NULL')
            ->andWhere('e.dateRetourPrevue < :now')
            ->setParameter('adherent', $adherent)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function countEnCours(): int
    {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->andWhere('e.dateRetourEffective IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countEnRetard(): int
    {
        return $this->createQueryBuilder('e')
            ->select('count(e.id)')
            ->andWhere('e.dateRetourEffective IS NULL')
            ->andWhere('e.dateRetourPrevue < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.document', 'd')
            ->leftJoin('e.adherent', 'a')
            ->addSelect('d', 'a')
            ->orderBy('e.dateEmprunt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByAdherentWithDocument(Adherent $adherent): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.document', 'd')
            ->addSelect('d')
            ->where('e.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('e.dateEmprunt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatut(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.statut, COUNT(e.id) as count')
            ->groupBy('e.statut')
            ->getQuery()
            ->getResult();
    }

    public function countByMonth(): array
    {
        return $this->createQueryBuilder('e')
            ->select('SUBSTRING(e.dateEmprunt, 6, 2) as mois, COUNT(e.id) as count')
            ->where('e.dateEmprunt >= :date')
            ->setParameter('date', new \DateTime('-12 months'))
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
