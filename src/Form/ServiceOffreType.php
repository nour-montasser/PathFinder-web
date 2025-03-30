<?php

namespace App\Form;

use App\Entity\Serviceoffre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ServiceoffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // You might want to remove id_user from the form if you set it automatically.
            ->add('id_user', IntegerType::class, [
                'label' => 'User ID'
            ])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            // date_posted is set automatically in the controller
            ->add('field', TextType::class)
            ->add('price', NumberType::class)
            ->add('required_education', TextType::class)
            ->add('skills', TextType::class)
            ->add('experience_level', TextType::class)
            ->add('duration', TextType::class)
            ->add('status', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Serviceoffre::class,
        ]);
    }
}
