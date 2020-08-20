<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderByType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('way', ChoiceType::class, [
                'choices' => [
                    'Rosnąco' => 'ASC',
                    'Malejąco' => 'DESC',
                ],
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('order', IntegerType::class, [
                'attr' => ['class' => 'form-control', 'maxlength' => 1, 'placeholder' => 'Podaj kolejność filtra'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}
