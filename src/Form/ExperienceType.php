<?php

namespace App\Form;

use App\Entity\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class, ['label' => 'Experience Type'])
            ->add('position', TextType::class, ['label' => 'Position'])
            ->add('location_name', TextType::class, ['label' => 'Location'])
            ->add('start_date', DateType::class, ['label' => 'Start Date'])
            ->add('end_date', DateType::class, ['label' => 'End Date'])
            ->add('description', TextareaType::class, ['label' => 'Description']);
    }
    

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }
}
