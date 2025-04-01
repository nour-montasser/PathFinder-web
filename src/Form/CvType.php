<?php

namespace App\Form;

use App\Entity\App_user;
use App\Entity\Cv;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_cv')
            ->add('title')
            ->add('user_title')
            ->add('introduction')
            ->add('date_creation', null, [
                'widget' => 'single_text',
            ])
            ->add('skills')
            ->add('last_viewed', null, [
                'widget' => 'single_text',
            ])
            ->add('favorite')
            ->add('user', EntityType::class, [
                'class' => App_user::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cv::class,
        ]);
    }
}
