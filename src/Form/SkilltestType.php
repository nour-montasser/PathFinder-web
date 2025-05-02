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

class SkilltestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('duration')
            ->add('score_required')

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
