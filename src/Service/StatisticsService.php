<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DocumentRepository;
use App\Repository\AdherentRepository;
use App\Repository\EmpruntRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class StatisticsService
{
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly EmpruntRepository $empruntRepository,
        private readonly CacheInterface $dashboardCache
    ) {}

    /**
     * KPIs du Dashboard (AVEC CACHE)
     */
    public function getDashboardKpis(): array
    {
        return $this->dashboardCache->get('dashboard_kpis', function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);

            return [
                'documents' => [
                    'total' => $this->documentRepository->count([]),
                    'disponibles' => $this->documentRepository->countDisponibles(),
                    'empruntes' => $this->documentRepository->count(['disponible' => false]),
                ],
                'adherents' => [
                    'total' => $this->adherentRepository->count([]),
                    'actifs' => $this->adherentRepository->countActifs(),
                ],
                'emprunts' => [
                    'en_cours' => $this->empruntRepository->countEnCours(),
                    'en_retard' => $this->empruntRepository->countEnRetard(),
                ],
            ];
        });
    }

    /**
     * Données pour graphiques Chart.js
     */
    public function getChartData(): array
    {
        return $this->dashboardCache->get('dashboard_charts', function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);

            return [
                'documents_par_type' => $this->documentRepository->countByType(),
                'emprunts_par_statut' => $this->empruntRepository->countByStatut(),
                'emprunts_par_mois' => $this->empruntRepository->countByMonth(),
            ];
        });
    }

    /**
     * Invalider le cache (appeler après modifications)
     */
    public function invalidateCache(): void
    {
        $this->dashboardCache->delete('dashboard_kpis');
        $this->dashboardCache->delete('dashboard_charts');
    }
}
