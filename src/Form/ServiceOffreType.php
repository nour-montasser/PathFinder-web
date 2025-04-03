<?php

namespace App\Form;

use App\Entity\Serviceoffre;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ServiceoffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user')
            ->add('description')
            ->add('title')
            ->add('field')
            ->add('price')
            ->add('required_education')
            ->add('skills')
            ->add('experience_level')
           
            ->add('status')
             // Mark startDate and endDate as unmapped so they're not tied to the entity
             ->add('startDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Start Date'],
                'required' => true,
            ])
            ->add('endDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control', 'placeholder' => 'End Date'],
                'required' => true,
            ])
            
            
    
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Serviceoffre::class,
        ]);
    }
}
