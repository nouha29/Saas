<?php

namespace App\Form;

use App\Entity\Stock;
use App\Entity\Articles;
use App\Entity\Depots;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => Articles::class,
                'choice_label' => 'reference',
                'attr' => ['class' => 'form-control'],
                'label' => 'Article'
            ])
            ->add('depot', EntityType::class, [
                'class' => Depots::class,
                'choice_label' => 'intitule',
                'attr' => ['class' => 'form-control'],
                'label' => 'Dépôt'
            ])
            ->add('dateEntree', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date d\'entrée'
            ])
            ->add('qteStockPrincipal', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'label' => 'Quantité stock principal'
            ])
            ->add('qteStockDispo', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'label' => 'Quantité disponible'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
