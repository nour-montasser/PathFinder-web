<?php

namespace App\Form;

use App\Entity\JobOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\AppUser; // Assuming you have an AppUser entity

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
                'choices' => [
                    'Full-time' => 'Full-time',
                    'Part-time' => 'Part-time',
                    'Fixed-term contract' => 'Fixed-term contract',
                    'Long-term contract' => 'Long-term contract'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('numberOfSpots', IntegerType::class, [
                'attr' => ['class' => 'form-control', 'min' => 1],
                'label' => 'Number of Spots'
            ])
            ->add('requiredEducation', ChoiceType::class, [
                'choices' => [
                    'High School' => 'High School',
                    'Bachelor\'s degree' => 'Bachelor\'s degree',
                    'Licence' => 'Licence',
                    'Master\'s degree' => 'Master\'s degree',
                    'Doctorate' => 'Doctorate',
                    'Postdoc' => 'Postdoc',
                    'PhD' => 'PhD'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('requiredExperience', TextType::class, [
                'attr' => ['class' => 'form-control']
            ])
            ->add('skills', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Skills (comma separated)'
            ])
            ->add('field', ChoiceType::class, [
                'choices' => [
                    'IT jobs' => 'IT jobs',
                    'Sales jobs' => 'Sales jobs',
                    'Unknown' => 'Unknown'
                ],
                'attr' => ['class' => 'form-select']
            ])
            // Add these new fields (they won't be mapped to the entity)
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
                    // Add more countries as needed
                ],
                'attr' => ['class' => 'form-select'],
                'required' => false,
                'mapped' => false,
                'placeholder' => 'Select a country'
            ])
              
            ->add('user', EntityType::class, [
                'class' => AppUser::class,
                'choice_label' => function(AppUser $user) {
                    return $user->getName(); // Assuming you have a getName() method
                },
                'placeholder' => 'Select a user',
                'attr' => ['class' => 'form-select'],
                'label' => 'Posted By'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}