<?php

namespace App\Form;

use App\Entity\Dette;
use App\Entity\Article;
use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'surname', // Affiche le nom du client
                'required' => true,
                'label' => 'Client',
                'attr' => ['class' => 'form-select'] // Bootstrap-friendly
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('montant', NumberType::class, [
                'label' => 'Montant Total',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0]
            ])
            ->add('montantVerset', NumberType::class, [
                'label' => 'Montant Versé',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0]
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État de la Dette',
                'choices' => [
                    'En Cours' => 'En Cours',
                    'Soldée' => 'Soldée',
                    'Annulée' => 'Annulée',
                ],
                'required' => true,
                'attr' => ['class' => 'form-select'] // Bootstrap-friendly
            ])
            ->add('dette_articles', EntityType::class, [
                'class' => Article::class,
                'choice_label' => function (Article $article) {
                    return sprintf('%s (Stock: %d)', $article->getNom(), $article->getQteStock());
                },
                'multiple' => true,
                'expanded' => true, // Affiche les articles sous forme de cases à cocher
                'required' => true,
                'label' => 'Articles Associés',
                'attr' => ['class' => 'form-check']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dette::class,
        ]);
    }
}
