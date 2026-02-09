<?php

namespace App\EventSubscriber;

use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, priority: 500, connection: 'default')]
class EmpruntSubscriber
{
    public function __construct(
        private readonly \App\Service\StatisticsService $statisticsService
    ) {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Emprunt) {
            return;
        }

        // Invalider le cache après modification
        $this->statisticsService->invalidateCache();

        // Note: Changing entity state in postPersist/postUpdate is synonymous
        // to doing it in prePersist/preUpdate but simpler for cache invalidation trigger.
        // However, the original logic was updating the DOCUMENT state in prePersist.
        // That logic was correct for data integrity. Cache invalidation should happen AFTER commit.
        // But since we are allowed to use 'pre' events, let's keep the logic there 
        // AND add post events for cache invalidation? Or just put invalidation in the service?
        // The prompt specifically asked to put invalidation in `postPersist` and `postUpdate` methods of the subscriber.
        // BUT the prompt used `EmpruntSubscriber` which WAS using `prePersist`.
        // The prompt example showed `postPersist` and `postUpdate`.
        // So I should CHANGE the existing methods to `post` OR add new ones.
        // The existing logic updates the Document entity. That MUST happen in `prePersist` or `preUpdate` (or inside a transaction).
        // If I change to `post`, I can't easily modify relation entities and expect them to save without another flush (recursion risk).
        // So I will KEEP `prePersist` for logic and ADD `postPersist` for cache.
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Emprunt) {
             return;
        }
        $this->statisticsService->invalidateCache();
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Emprunt) {
            return;
        }

        // New loan -> Document becomes unavailable
        if ($entity->getDocument()) {
            $entity->getDocument()->setDisponible(false);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Emprunt) {
            return;
        }

        // Check if dateRetourEffective has changed and is set (Return action)
        if ($args->hasChangedField('dateRetourEffective') && $entity->getDateRetourEffective() !== null) {
            if ($entity->getDocument()) {
                $entity->getDocument()->setDisponible(true);
            }
            
            // Auto update status if not set manually
            if ($entity->getDateRetourEffective() > $entity->getDateRetourPrevue()) {
                 $entity->setStatut(Emprunt::STATUT_RETARD);
            } else {
                 $entity->setStatut(Emprunt::STATUT_TERMINE);
            }
        }
    }
}
