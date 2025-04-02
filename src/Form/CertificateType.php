<?php

namespace App\Form;

use App\Entity\Certificates;
use App\Entity\Cv;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_certificate')
            ->add('title')
            ->add('description')
            ->add('media')
            ->add('issue_date', null, [
                'widget' => 'single_text',
            ])
            ->add('issued_by')
            ->add('cv', EntityType::class, [
                'class' => Cv::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Certificates::class,
        ]);
    }
}
