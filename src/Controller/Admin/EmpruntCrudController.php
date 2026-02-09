<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use App\Entity\User;
use App\Service\EmpruntService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_BIBLIOTHECAIRE)]
class EmpruntCrudController extends AbstractCrudController
{
    public function __construct(
        private EmpruntService $empruntService
    ) {}

    public static function getEntityFqcn(): string
    {
        return Emprunt::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Emprunt')
            ->setEntityLabelInPlural('Emprunts')
            ->setPageTitle('index', 'Gestion des Emprunts')
            ->setDefaultSort(['dateEmprunt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        
        yield AssociationField::new('adherent')
            ->setLabel('Adhérent')
            ->setRequired(true);

        yield AssociationField::new('document')
            ->setLabel('Document')
            ->setRequired(true)
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                // Only show available documents in the dropdown for NEW loans?
                // Warning: For EDITing existing loans, we need to find the current doc too.
                // Keeping it simple for now, EmpruntService validation will catch it.
                return $queryBuilder->orderBy('entity.titre', 'ASC');
            });

        yield DateField::new('dateEmprunt')->setRequired(true);
        yield DateField::new('dateRetourPrevue')->setRequired(true);
        yield DateField::new('dateRetourEffective')->setLabel('Retour Effectif');

        yield ChoiceField::new('statut')->setChoices([
            'En cours' => Emprunt::STATUT_EN_COURS,
            'Terminé' => Emprunt::STATUT_TERMINE,
            'En retard' => Emprunt::STATUT_RETARD,
        ])->renderAsBadges([
            Emprunt::STATUT_EN_COURS => 'warning',
            Emprunt::STATUT_TERMINE => 'success',
            Emprunt::STATUT_RETARD => 'danger',
        ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Emprunt) return;

        try {
            // We use the service to CREATE the loan properly
            // BUT EasyAdmin has already populated the $entityInstance with data from the form.
            // So we can pass the Adherent and Document to the service.
            // The service creates a NEW instance. We should probably just use the logic from service
            // but apply it to this instance?
            // Actually, EmpruntService::creerEmprunt creates a new object.
            // Better to manually call checking logic here if we want to use EasyAdmin's object.
            
            if (!$this->empruntService->canEmprunter($entityInstance->getAdherent(), $entityInstance->getDocument())) {
                $this->addFlash('danger', "Impossible de créer cet emprunt : Adhérent invalide, retards, ou document indisponible.");
                // We don't persist. This will result in no action, but EasyAdmin might think it worked? 
                // There is no easy way to stop flow here without Exception.
                throw new \RuntimeException("Règles métier non respectées (Retards, Inactif, Indisponible).");
            }
            
            // Logic for availability is handled by Subscriber on persist.
            parent::persistEntity($entityManager, $entityInstance);
            
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }
}
