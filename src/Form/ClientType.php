<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\User;
use App\Form\AdresseType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Nom du client
            ->add('surname', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom ne peut pas être vide.']),
                ],
            ])
            // Téléphone avec validation de format
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire.']),
                    new Regex([
                        'pattern' => '/^\\d{8,15}$/',
                        'message' => 'Le numéro de téléphone doit comporter entre 8 et 15 chiffres.',
                    ]),
                ],
            ])
            // Adresse sous-formulaire
            ->add('adresse', AdresseType::class, [
                'label' => 'Adresse',
            ])
            // Associer un utilisateur existant (optionnel)
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // Affiche l'email de l'utilisateur
                'required' => false,
                'label' => 'Associer un compte utilisateur (optionnel)',
                'placeholder' => 'Aucun compte associé',
                'attr' => ['class' => 'form-control'],
            ])
            // Champ pour afficher le cumul des montants dus (readonly)
            ->add('cumulMontantsDus', NumberType::class, [
                'label' => 'Cumul des Montants Dus',
                'mapped' => false, // Ce champ n'est pas lié directement à l'entité
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
