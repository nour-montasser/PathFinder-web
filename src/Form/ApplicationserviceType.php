<?php
// src/Form/ApplicationserviceType.php

namespace App\Form;

use App\Entity\Applicationservice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ApplicationserviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('priceOffre', MoneyType::class, [
            'currency' => 'USD',
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Enter a valid price.']),
                new Assert\GreaterThan([
                    'value' => 0,
                    'message' => 'Price must be higher than 0.'
                ])
            ]
        ])
            
            ->add('description', TextareaType::class, [
                'label'    => 'Your Proposal',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'rows'        => 5,
                    'placeholder' => 'Briefly explain your approach…'
                ],
            ])
            ->add('portfolio', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Url(['message' => 'Link mut be a valid URL.'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Applicationservice::class,
        ]);
    }
}
