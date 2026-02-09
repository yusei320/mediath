<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted(User::ROLE_ADMIN)]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des Utilisateurs')
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        
        $roles = [
            'Administrateur' => User::ROLE_ADMIN,
            'Bibliothécaire' => User::ROLE_BIBLIOTHECAIRE,
            'Adhérent' => User::ROLE_ADHERENT,
            'Utilisateur' => User::ROLE_USER,
        ];

        yield ChoiceField::new('roles')
            ->setChoices($roles)
            ->allowMultipleChoices()
            ->renderAsBadges();

        yield TextField::new('password')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp('Laissez vide pour conserver le mot de passe actuel (en modification).');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encryptPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encryptPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function encryptPassword(User $user): void
    {
        if ($user->getPassword() !== null && $user->getPassword() !== '') {
            // Check if it's already a hash? No, here we assume clear text from form.
            // But on update, if field is empty, it might be null.
            // EasyAdmin doesn't map empty field to entity property if not submitted?
            // Actually, if TextField is mapped to 'password', it overwrites.
            // We need to check if the input is meant to be a new password.
            // Basic logic: if non-empty, hash it.
            $hashed = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashed);
        }
    }
}
