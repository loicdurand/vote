<?php

namespace App\Form;

use App\Entity\Election;
use App\Entity\Organizer;
use App\Entity\Groupe;
use Dom\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'row_attr' => ['class' => 'fr-mt-2w'],
                'choice_label' => 'name',
                'label' => 'Unité organisatrice du vote',
            ])
            ->add('title', null, [
                'help_attr' => ['content' => 'Obligatoire. 255 caractères maximum'],
                'label' => 'Titre de l\'élection',
                "required" => true,
                'attr' => [
                    'placeholder' => 'Élection du conseil d\'administration de ...',
                    // 'class' => 'fr-input'
                ]
            ])
            ->add('explaination', TextareaType::class, [
                'help_attr' => ['content' => 'Facultatif. 1024 caractères maximum'],
                'label' => 'Renseignements utiles pour les électeurs',
                "required" => false,
                'attr' => [
                    'placeholder' => 'Note de service, courriel en date du ...',
                    'class' => 'fr-input'
                ]
            ])
            ->add('startDate', null, [
                // 'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                // 'help_attr' => ['content' => 'Faculatif.'],
                "required" => true,
                'label' => 'Date et heure d\'ouverture des votes',
            ])
            ->add('endDate', null, [
                // 'row_attr' => ['class' => 'fr-col-6 fr-col-sm-6'],
                // 'help_attr' => ['content' => 'Faculatif.'],
                "required" => true,
                'label' => 'Date et heure de fermeture des votes',
            ])
            ->add('groupesConcernes', EntityType::class, [
                'class' => Groupe::class,
                "multiple" => true,
                // 'row_attr' => ['class' => 'fr-mt-2w'],
                'help_attr' => ['content' => 'Un choix minimum. Les membres des groupes non sélectionnés ne pourront pas participer au vote.'],
                'choice_label' => 'name',
                "required" => true,
                'label' => 'Groupes concernés par l\'élection',
            ])
            ->add('unitesConcernees', EntityType::class, [
                'class' => Organizer::class,
                "multiple" => true,
                // 'row_attr' => ['class' => 'fr-mt-2w'],
                'help_attr' => ['content' => 'Un choix minimum. Les personnels des unités non sélectionnées ne pourront pas participer au vote.'],
                "required" => true,
                'choice_label' => 'name',
                'label' => 'Unités concernées par l\'élection',
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
