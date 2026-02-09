<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_BIBLIOTHECAIRE)]
class AdherentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Adherent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Adhérent')
            ->setEntityLabelInPlural('Adhérents')
            ->setSearchFields(['nom', 'prenom', 'email'])
            ->setDefaultSort(['nom' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('actif')
            ->add('dateInscription');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('nom');
        yield TextField::new('prenom');
        yield EmailField::new('email');
        yield TelephoneField::new('telephone');
        yield TextField::new('adresse')->hideOnIndex();
        yield DateField::new('dateInscription');
        yield BooleanField::new('actif');
        
        yield AssociationField::new('emprunts')
            ->setLabel('Emprunts')
            ->onlyOnDetail(); // Show only in detail view to avoid clutter
    }
}
