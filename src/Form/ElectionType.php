<?php

namespace App\Form;

use App\Entity\Election;
use App\Entity\Organizer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('organizer', EntityType::class, [
                'class' => Organizer::class,
                // 'row_attr' => ['class' => 'fr-mt-2w'],
                'choice_label' => 'name',
                'label' => 'Unité organisatrice du vote',
            ])
            ->add('title', null, [
                'help_attr' => ['content' => 'Obligatoire. 255 caractères maximum'],
                'label' => 'Titre de l\'élection',
                'attr' => [
                    'placeholder' => 'Élection du conseil d\'administration de ...',
                    // 'class' => 'fr-input'
                ]
            ])
            ->add('explaination', TextareaType::class, [
                'help_attr' => ['content' => 'Facultatif. 1024 caractères maximum'],
                'label' => 'Renseignements utiles pour les électeurs',
                'attr' => [
                    'placeholder' => 'Note de service, courriel en date du ...',
                    'class' => 'fr-input'
                ]
            ])
            ->add('startDate', null, [
                // 'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                // 'help_attr' => ['content' => 'Faculatif.'],
                'label' => 'Date et heure d\'ouverture des votes',
            ])
            ->add('endDate', null, [
                // 'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                // 'help_attr' => ['content' => 'Faculatif.'],
                'label' => 'Date et heure de fermeture des votes',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Election::class,
        ]);
    }
}
