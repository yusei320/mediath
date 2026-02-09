<?php

namespace App\Form;

use App\Entity\DemandeEmprunt;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class DemandeEmpruntType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bibliothecaire', EntityType::class, [
                'class' => User::class,
                'choices' => $options['bibliothecaires'],
                'choice_label' => function (User $user) {
                    return $user->getEmail(); // Ou nom/prénom si dispo
                },
                'label' => 'Bibliothécaire',
                'placeholder' => 'Choisissez un bibliothécaire',
            ])
            ->add('dateEmpruntSouhaitee', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date d\'emprunt souhaitée',
                'data' => new \DateTime(),
            ])
            ->add('dureeSouhaiteeJours', ChoiceType::class, [
                'choices' => [
                    '7 jours' => 7,
                    '14 jours' => 14,
                    '21 jours' => 21,
                    '30 jours' => 30,
                ],
                'data' => 14,
                'label' => 'Durée souhaitée',
            ])
            ->add('messageAdherent', TextareaType::class, [
                'required' => false,
                'label' => 'Message (optionnel)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeEmprunt::class,
            'bibliothecaires' => [],
        ]);
        
        $resolver->setAllowedTypes('bibliothecaires', 'array');
    }
}
