<?php

namespace App\Form;

use App\Entity\Job_offer;
use App\Entity\Skilltest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\QuestionsType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class SkilltestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 5, 'max' => 255]), // Add max length
            ]
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10]),
                ]
            ])
            ->add('duration', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Positive(),
                ]
            ])
            ->add('score_required', IntegerType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 100]),
                ]
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionsType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skilltest::class,
        ]);
    }
}
