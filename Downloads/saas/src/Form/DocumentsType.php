<?php

namespace App\Form;

use App\Entity\Documents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Users;

class DocumentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', null, [
                'attr' => ['class' => 'd-none'],
                'label' => false,
                'disabled' => true,
            ])
            ->add('docDate', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'label' => 'Date du document'
            ])
            ->add('emetteur', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'username',
                'attr' => ['class' => 'form-control'],
                'label' => 'Émetteur'
            ])
            ->add('destinataire', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'username',
                'attr' => ['class' => 'form-control'],
                'label' => 'Destinataire'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Devis achat' => 'DA',
                    'Commande achat' => 'CA',
                    'Facture achat' => 'FA',
                    'Facture achat avoire' => 'FAA',
                    'Bon d\'entrée' => 'BE',
                    'Bon de transfert' => 'BT',
                    'Bon de retour' => 'BR',
                    'Devis vente' => 'DV',
                    'Commande vente' => 'CV',
                    'Facture vente' => 'FV',
                    'Facture vente avoire' => 'FVA',
                    'Bon de sortie' => 'BS',
                    'Bon de livraison' => 'BL',
                    'Inventaire' => 'Inv'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'onchange' => 'updateReferencePreview()',
                    'style' => $options['hide_type'] ? 'display:none;' : ''
                ],
                'label' => $options['hide_type'] ? false : 'Type',
                'disabled' => $options['hide_type']
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Sous traitement' => 'Sous traitement',
                    'Ouvert' => 'Ouvert',
                    'Confirmé' => 'Confirmé',
                    'Cloturé' => 'Cloturé',
                ],
                'attr' => ['class' => 'form-control'],
                'label' => 'Statut'
            ])
            ->add('tauxTva', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'onchange' => 'updateTotals()'
                ],
                'label' => 'Taux TVA (%)',
                'required' => false
            ])
            ->add('montantHt', NumberType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'readonly' => 'readonly'
                ],
                'label' => 'Montant HT',
                'required' => false
            ])
            ->add('montantTva', NumberType::class, [ // Renommé en TTC
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'readonly' => 'readonly'
                ],
                'label' => 'Montant TTC',
                'required' => false
            ])
            ->add('timbre', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'onchange' => 'updateTotals()'
                ],
                'label' => 'Timbre',
                'required' => false
            ])
            ->add('retenu', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'onchange' => 'updateTotals()'
                ],
                'label' => 'Retenue',
                'required' => false
            ])
            ->add('montantAPayer', NumberType::class, [
                'attr' => [
                    'class' => 'form-control bg-light border-0',
                    'readonly' => 'readonly'
                ],
                'label' => 'Montant à payer',
                'required' => false
            ])
            ->add('lignes', CollectionType::class, [
                'entry_type' => DocumentsligneType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'attr' => ['class' => 'lignes-collection'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Documents::class,
            'hide_type' => false
        ]);
    }
}
