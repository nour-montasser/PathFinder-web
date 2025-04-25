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



class ServiceoffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           
            ->add('description')
            ->add('generateDescription', SubmitType::class, [
                'label' => 'Generate with AI',
                'attr' => [
                    'class' => 'btn btn-secondary',
                    'formnovalidate' => 'formnovalidate',
                    'onclick' => 'event.preventDefault(); generateDescription(this.form);'
                ],
                'validate' => false
            ])
            ->add('title')
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
                'label' => 'Prix',
                'currency' => 'EUR', // or 'USD' based on your app
                'required' => false,
                
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
                'attr' => ['class' => 'form-control']
            ])
           
            ->add('status')
             // Mark startDate and endDate as unmapped so they're not tied to the entity
             ->add('startDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
                'constraints' => [
                    new Assert\NotBlank(message: 'La date de début est requise.'),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de début doit être aujourd\'hui ou une date future.'
                    ]),
                ]
                
            ])
            ->add('endDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d')],
                'constraints' => [
                    new Assert\NotBlank(message: 'La date de fin est requise.'),
                ]
            ])
            ->add('price_estimation', NumberType::class, [
                'required' => false, // 💡 prevent validation when AI button is clicked
                'label' => 'Price (Estimated)',
            ])
            ->add('generatePrice', SubmitType::class, [
                'label' => 'Estimate Price with AI',
                'attr' => ['formnovalidate' => 'formnovalidate']
            ]);
            
            
        
            
            
    
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Serviceoffre::class,
        ]);
    }
}
