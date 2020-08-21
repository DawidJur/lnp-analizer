<?php

namespace App\Form;

use App\Entity\League;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class FindPlayersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('playerName', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Regex('/\b[a-zA-Z ]{1,100}$/')
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Imię i nazwisko',
                ],
            ])
            ->add('orderByPlayer', OrderByType::class)
            ->add('ageFrom', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Wiek od',
                ],
            ])
            ->add('ageTo', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Wiek do',
                ],
            ])
            ->add('orderByAge', OrderByType::class)
            ->add('goalsFrom', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ilość goli od',
                ],
            ])
            ->add('goalsTo', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ilość goli do',
                ],
            ])
            ->add('orderByGoals', OrderByType::class)
            ->add('minutesFrom', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ilość minut od',
                ],
            ])
            ->add('orderByMinutes', OrderByType::class)
            ->add('minutesTo', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ilość minut do',
                ],
            ])
            ->add('dateFrom', DateType::class, [
                'required' => false,
                'placeholder' => [
                    'year' => 'Rok', 'month' => 'Miesiąc', 'day' => 'Dzień',
                ],
                'format' => 'dd-MM-yyyy',
            ])
            ->add('dateTo', DateType::class, [
                'required' => false,
                'placeholder' => [
                    'year' => 'Rok', 'month' => 'Miesiąc', 'day' => 'Dzień',
                ],
                'format' => 'dd-MM-yyyy',
                'row_attr' => ['class' => 'form-control'],
            ])
            ->add('league', EntityType::class, [
                'class' => League::class,
                'choice_label' => 'name',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('season', ChoiceType::class, [
                'choices' => [
                    'Dowolny sezon' => 'any',
                    'Grupuj wyniki po sezonie' => 'group',
                    '2020/2021' => 'SEZON 2020/2021',
                    '2019/2020' => 'SEZON 2019/2020',
                    '2018/2019' => 'SEZON 2018/2019', //todo use some kind of const or DB
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('page', IntegerType::class, [
                'attr' => ['value' => 1, 'class' => 'form-control'],

            ])
            ->add('limit', ChoiceType::class, [
                'choices' => [
                    25 => 25,
                    50 => 50,
                    100 => 100,
                    250 => 250,
                ],
                'attr' => ['class' => 'form-control'],

            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET'
        ]);
    }
}
