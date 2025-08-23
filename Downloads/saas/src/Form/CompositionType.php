<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Entity\Articles;
use App\Entity\Composition;

class CompositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matiere', EntityType::class, [
                'class' => Articles::class,
                'choice_label' => 'reference',
                'label' => 'Matière première',
                'placeholder' => 'Sélectionnez une matière',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.type = :type')
                        ->setParameter('type', 'Matière Première');
                },
                'attr' => ['class' => 'form-select matiere-select'],
                'choice_attr' => function ($choice) {
                    return ['data-unite' => $choice->getUnite()];
                },
            ])
            ->add('consommation', NumberType::class, [
                'label' => 'Consommation',
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Composition::class,
        ]);
    }
}
