<?php
// src/Form/CvType.php

namespace App\Form;

use App\Entity\Cv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_title', TextType::class, [
                'label'       => 'CV Title',
                'required'    => true,
                'empty_data'  => '',
                'attr'        => [
                    'maxlength'   => 100,
                    'placeholder' => 'Enter CV Title',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a title for your CV.']),
                ],
            ])
            ->add('introduction', TextareaType::class, [
                'label'       => 'Introduction',
                'required'    => true,
                'empty_data'  => '',
                'attr'        => [
                    'rows'        => 3,
                    'maxlength'   => 300,
                    'placeholder' => 'Enter CV Introduction',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an introduction.']),
                ],
            ])
            ->add('skills', HiddenType::class, [
                'required'    => true,
                'empty_data'  => '',
                'mapped'      => true,
                'constraints' => [
                    new NotBlank(['message' => 'Please add at least one skill.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cv::class,
        ]);
    }
}
