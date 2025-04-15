<?php

namespace App\Form;

use App\Entity\Languages;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class LanguagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('language_name', TextType::class, ['label' => 'Language'])
        ->add('level', ChoiceType::class, [
            'choices' => [
                'Beginner' => 'Beginner',
                'Intermediate' => 'Intermediate',
                'Advanced' => 'Advanced',
                'Expert' => 'Expert'
            ],
            'label' => 'Proficiency Level',
        ]);
}


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Languages::class,
        ]);
    }
}


