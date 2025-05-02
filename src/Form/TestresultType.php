<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Test_result;

class TestresultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    '⭐⭐⭐⭐⭐' => 5,
                    '⭐⭐⭐⭐' => 4,
                    '⭐⭐⭐' => 3,
                    '⭐⭐' => 2,
                    '⭐' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Test_result::class,
        ]);
    }
}
