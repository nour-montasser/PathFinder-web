<?php

namespace App\Form;

use App\Entity\Job_offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\App_user; // Assuming you have an AppUser entity

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(Job_offer::JOB_TYPES, Job_offer::JOB_TYPES),
                'attr' => ['class' => 'form-select']
            ])
            ->add('number_of_spots', IntegerType::class, [
                'attr' => ['class' => 'form-control', 'min' => 1],
                'label' => 'Number of Spots',
            ])
            ->add('required_education', ChoiceType::class, [
                'choices' => array_combine(Job_offer::EDUCATION_LEVELS, Job_offer::EDUCATION_LEVELS),
                'attr' => ['class' => 'form-select'],
                'property_path' => 'required_education'
            ])
            ->add('required_experience', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'property_path' => 'required_experience'
            ])
            ->add('skills', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Skills (comma separated)'
            ])
            ->add('field', ChoiceType::class, [
                'choices' => array_combine(Job_offer::FIELDS, Job_offer::FIELDS),
                'attr' => ['class' => 'form-select']
            ])
            // Champs non mappés pour la ville et le pays
            ->add('city', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'mapped' => false
            ])
            ->add('country', ChoiceType::class, [
                'choices' => [
                    'United States' => 'United States',
                    'Canada' => 'Canada',
                    'United Kingdom' => 'United Kingdom',
                    'France' => 'France',
                ],
                'attr' => ['class' => 'form-select'],
                'required' => false,
                'mapped' => false,
                'placeholder' => 'Select a country'
            ])
            ->add('user', EntityType::class, [
                'class' => App_user::class,
                'choice_label' => function(App_user $user) {
                    return $user->getName(); // Assurez-vous que la méthode existe
                },
                'placeholder' => 'Select a user',
                'attr' => ['class' => 'form-select'],
                'label' => 'Posted By'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job_offer::class,
            'allow_extra_fields' => true,
        ]);
    }
}
