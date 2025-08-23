<?php
namespace App\Form;

use App\Entity\Articles;
use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticlesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Produit Fini' => 'Produit Fini',
                    'Matière Première' => 'Matière Première',
                ],
                'label' => 'Type d\'article',
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un type d\'article',
                    ]),
                ],
                'placeholder' => 'Sélectionnez un type'
            ])
            ->add('fournisseur', EntityType::class, [
                'class' => Users::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.profile = :profileId')
                        ->setParameter('profileId', 6)
                        ->orderBy('u.username', 'ASC');
                },
                'choice_label' => function(Users $user) {
                    return $user->getUsername();
                },
                'label' => 'Fournisseur',
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un fournisseur',
                    ]),
                ],
                'placeholder' => 'Sélectionnez un fournisseur'
            ])
            ->add('unite', ChoiceType::class, [
                'choices' => [
                    'Kilogramme (kg)' => 'kg',
                    'Litre (l)' => 'l',
                    'Pièce (pce)' => 'pce',
                    'Mètre cube (m³)' => 'm³',
                ],
                'label' => 'Unité de mesure',
                'attr' => [
                    'class' => 'form-select',
                    'data-controller' => 'select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une unité de mesure',
                    ]),
                ],
                'placeholder' => 'Sélectionnez une unité'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
            'attr' => ['id' => 'article-form']
        ]);
    }
}