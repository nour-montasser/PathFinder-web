<?php

namespace App\Form;

use App\Entity\Serviceoffre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceOffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description', null, [
    'required' => false,
])

            ->add('field', ChoiceType::class, [
                'label' => 'Field',
                'choices' => [
                    'Design' => 'Design',
                    'Architecture' => 'Architecture',
                    'Engineering' => 'Engineering',
                    'Marketing' => 'Marketing',
                    'IT & Software' => 'IT & Software',
                    'Writing' => 'Writing',
                    'Business' => 'Business',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Choose a field...',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Price',
                'currency' => 'EUR',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            ->add('required_education')
            ->add('skills')
            ->add('experience_level', ChoiceType::class, [
                'label' => 'Experience Level',
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                    'Expert' => 'Expert',
                ],
                'placeholder' => 'Select experience level...',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status')
            ->add('startDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Start date is required.']),
                    new Assert\GreaterThanOrEqual(['value' => 'today', 'message' => 'Start date must be today or in the future.']),
                ],
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
            ])
            ->add('endDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'End date is required.']),
                ],
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Serviceoffre::class,
        ]);
    }
}
