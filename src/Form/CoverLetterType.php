<?php
namespace App\Form;

use App\Entity\Coverletter;
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
                    'class' => 'form-control',
                    'placeholder' => 'Application for {position}'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'constraints' => [
                    new NotBlank(['message' => 'Cover letter cannot be empty']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'data-controller' => 'textarea-autosize'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coverletter::class,
            'empty_data' => function () {
                $coverletter = new Coverletter();
                $coverletter->setSubject('');
                $coverletter->setContent('');
                return $coverletter;
            }
        ]);
    }
}