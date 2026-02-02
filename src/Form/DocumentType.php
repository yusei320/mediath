<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Titre du document']
            ])
            ->add('auteur', TextType::class, [
                'label' => 'Auteur',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom de l\'auteur']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Livre' => 'Livre',
                    'DVD' => 'DVD',
                    'CD' => 'CD',
                    'Magazine' => 'Magazine',
                    'BD' => 'BD',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '978-X-XXX-XXXXX-X']
            ])
            ->add('dateAcquisition', DateType::class, [
                'label' => 'Date d\'acquisition',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('disponible', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('resume', TextareaType::class, [
                'label' => 'Résumé',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Résumé ou description du document']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}