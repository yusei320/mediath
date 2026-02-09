<?php

namespace App\Controller\Admin;

use App\Entity\Document;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_BIBLIOTHECAIRE)]
class DocumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Document')
            ->setEntityLabelInPlural('Documents')
            ->setSearchFields(['titre', 'auteur', 'isbn'])
            ->setDefaultSort(['titre' => 'ASC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('type')
            ->add('auteur')
            ->add('disponible');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('titre');
        yield TextField::new('auteur');
        
        yield ChoiceField::new('type')->setChoices([
            'Livre' => Document::TYPE_LIVRE,
            'CD' => Document::TYPE_CD,
            'DVD' => Document::TYPE_DVD,
            'Magazine' => Document::TYPE_MAGAZINE,
            'BD' => Document::TYPE_BD,
        ]);

        yield TextField::new('isbn')->setLabel('ISBN');
        yield DateField::new('dateAcquisition');
        yield BooleanField::new('disponible')->renderAsSwitch(false); // Read only mostly, changed by loans
        yield TextareaField::new('resume')->hideOnIndex();
    }
}
