<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class NewLeagueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newLeagueUrl', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex('/\b(https:\/\/www.laczynaspilka.pl\/rozgrywki\/)[a-z-]{0,},[0-9]{1,15}(.html)$/')
                ],
                'attr' => [
                    'class' => 'form-control url_input',
                    'placeholder' => 'Link do ligi',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Zapisz ligę',
                'attr' => [
                    'class' => 'btn btn-info',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
