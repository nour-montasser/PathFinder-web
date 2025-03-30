<?php

namespace App\Form;

use App\Entity\AppUser;
use App\Entity\JobOffer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('type')
            ->add('number_of_spots', IntegerType::class)
            ->add('required_education')
            ->add('required_experience')
            ->add('skills')
            ->add('field')
            ->add('address')
            ->add('user', EntityType::class, [
                'class' => AppUser::class,
                'choice_label' => 'id',
                'choice_value' => 'id'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}
