<?php

namespace App\Form;

use App\Entity\Election;
use App\Entity\Unite;
use App\Entity\Groupe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'empty_data' => '',
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
            // <input  type="checkbox" aria-describedby="checkbox-select-groups-messages" onchange="document.querySelector('.select-groups').classList.toggle('fr-hidden');">
            ->add('one_election_by_group', CheckboxType::class, [
                'row_attr' => [
                    'aria-describedby' => "checkbox-select-groups-messages",
                    'name' => 'one_election_by_group'
                ],
                'label' => 'Je veux créer une élection distincte pour chaque corps d\'appartenance.',
                'required' => false,
                'mapped' => false // Indique que ce champ n'est pas lié à l'entité
            ])
            ->add('groupesConcernes', EntityType::class, [
                'class' => Groupe::class,
                "multiple" => true,
                'row_attr' => ['class' => 'select-groups'],
                'help_attr' => ['content' => 'Un choix minimum. Les membres des corps d\'appartenance non sélectionnés ne pourront pas participer au vote.'],
                'choice_label' => 'name',
                "required" => false,
                'label' => 'Corps d\'appartenance concernés par l\'élection',
            ])
            ->add('unitesConcernees', EntityType::class, [
                'class' => Unite::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                        ->andWhere('u.departement = :val')
                        ->setParameter('val', 971)
                        ->orderBy('u.name', 'ASC');
                },
                "multiple" => true,
                // 'row_attr' => ['class' => 'fr-mt-2w'],
                'help_attr' => ['content' => 'Un choix minimum. Les personnels des unités non sélectionnées ne pourront pas participer au vote.'],
                "required" => true,
                'choice_label' => 'name',
                'label' => 'Unités concernées par l\'élection',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Election::class,
        ]);
    }
}
