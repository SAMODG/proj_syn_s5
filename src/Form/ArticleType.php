<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'article',
                'attr' => ['class' => 'form-control']
            ])
            ->add('qteStock', IntegerType::class, [
                'label' => 'Quantité en Stock',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'La quantité en stock doit être positive ou nulle.',
                    ]),
                ],
            ])

            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control']
            ])
          ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
