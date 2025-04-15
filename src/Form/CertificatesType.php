<?php


namespace App\Form;

use App\Entity\Certificates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CertificatesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Certificate Title'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('media', TextType::class, ['label' => 'Media'])
            ->add('issue_date', DateType::class, ['label' => 'Issue Date'])
            ->add('issued_by', TextType::class, ['label' => 'Issued By']);
    }
    

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Certificates::class,
        ]);
    }
}