<?php
namespace App\Form;

use App\Entity\Coverletter;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CoverLetterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('subject', TextType::class, [
            'label' => 'Subject',
            'constraints' => [
                new NotBlank(['message' => 'Please enter a subject']),
            ],
            'attr' => [
                'class' => 'form-control mb-3',
                'placeholder' => 'Application for {position}'
            ]
        ])
        ->add('content', CKEditorType::class, [
            'attr' => [
                'class' => 'form-control',
                'rows' => 10,
                'id' => 'coverletter_editor'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coverletter::class,
        ]);
    }

    public function getBlockPrefix()
{
    return 'coverletter';
}
}