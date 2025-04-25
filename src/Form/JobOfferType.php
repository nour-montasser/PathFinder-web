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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Retrieve countries passed as form option
        $countryChoices = $options['countries'];
    $currentCountry = $options['current_country'];
    $currentCity = $options['current_city'];

        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a job title']),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Job title cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a job description']),
                    new Length([
                        'min' => 50,
                        'minMessage' => 'Description should be at least {{ limit }} characters long'
                    ])
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => array_combine(Job_offer::JOB_TYPES, Job_offer::JOB_TYPES),
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a job type'])
                ]
            ])
            ->add('number_of_spots', IntegerType::class, [
                'attr' => ['class' => 'form-control', 'min' => 1],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter number of positions']),
                    new Positive(['message' => 'Number must be positive'])
                ]
            ])
            ->add('required_education', ChoiceType::class, [
                'choices' => array_combine(Job_offer::EDUCATION_LEVELS, Job_offer::EDUCATION_LEVELS),
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Please select required education'])
                ]
            ])
            ->add('required_experience', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter required experience'])
                ]
            ])
            ->add('skills', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter required skills'])
                ]
            ])
            ->add('field', ChoiceType::class, [
                'choices' => array_combine(Job_offer::FIELDS, Job_offer::FIELDS),
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a field'])
                ]
            ])
            ->add('country', ChoiceType::class, [
                'choices' => $options['countries'], // Keep original structure: ['CountryName' => 'CountryCode']
                'choice_label' => function ($value, $key, $index) {
                    return $key; // Show country names in dropdown
                },
                'placeholder' => 'Select a country',
                'mapped' => false,
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a country'])
                ]
            ])
            ->add('city', TextType::class, [ // Changed from ChoiceType to TextType
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control d-none', // Hide the input
                    'data-city-target' => 'input' // Add data attribute for JavaScript
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a city'])
                ]
            ])
            ->add('city_display', TextType::class, [ // New field for display only
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'data-city-target' => 'display'
                ]
            ])
            ->add('address', HiddenType::class, [
                'attr' => ['class' => 'd-none']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Job_offer::class,
            'allow_extra_fields' => true,
            'countries' => [], // This will be passed from controller
            'current_country' => null,
            'current_city' => null
        ]);
    }
}
