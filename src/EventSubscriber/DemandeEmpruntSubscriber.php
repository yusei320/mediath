<?php

namespace App\EventSubscriber;

use App\Entity\DemandeEmprunt;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use App\Service\StatisticsService;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
class DemandeEmpruntSubscriber
{
    public function __construct(
        private readonly StatisticsService $statisticsService
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->invalidateIfDemande($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->invalidateIfDemande($args->getObject());
    }

    private function invalidateIfDemande(object $entity): void
    {
        if ($entity instanceof DemandeEmprunt) {
            // Invalidate cache possibly if we add KPIs about requests
            // For now, prompt requested just to invalidate cache when treated
            $this->statisticsService->invalidateCache();
        }
    }
}
