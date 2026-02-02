<?php

namespace App\Controller\Admin;

use App\Entity\Emprunt;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class EmpruntCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Emprunt::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('dateEmprunt'),
            DateField::new('dateRetourPrevu'),
            DateField::new('dateRetourEffectif'),
            ChoiceField::new('statut')->setChoices([
                'En cours' => 'en_cours',
                'Terminé' => 'termine',
                'Retard' => 'retard',
            ]),
            AssociationField::new('adherent'),
            AssociationField::new('document'),
        ];
    }
}
