<?php
namespace App\Form;

use App\Entity\ApplicationJob;
use App\Entity\Coverletter;
use App\Entity\Cv;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use App\Form\CoverLetterType;


class ApplicationJobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $currentStep = $options['current_step'];
    
    // Step 1: Only add CV selection
    $builder->add('cv', EntityType::class, [
        'class' => Cv::class,
        'choice_label' => 'title',
        'label' => 'Choose your CV',
        'placeholder' => 'Select CV',
        'constraints' => [
            new NotBlank(['message' => 'Please select a CV']),
        ],
        'choices' => $options['available_cvs'],
        'attr' => [
            'class' => 'form-select form-select-lg'
        ]
    ]);
    
    // Step 2+: Add cover letter fields
    if ($currentStep >= 2) {
        $builder->add('coverletter', CoverLetterType::class, [
            'label' => false,
            'required' => false
        ]);
    }
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationJob::class,
            'available_cvs' => [],
            'current_step' => 1, // Default to step 1
        ]);
        $resolver->setAllowedTypes('current_step', 'int');
    }
}