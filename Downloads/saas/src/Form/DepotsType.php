<?php

namespace App\Form;

use App\Entity\Depots;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class DepotsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule', TextType::class, [
                'label' => 'Intitulé du dépôt',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'intitulé du dépôt'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un intitulé',
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Depots::class,
        ]);
    }
}