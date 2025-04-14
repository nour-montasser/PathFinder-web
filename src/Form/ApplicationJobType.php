<?php
namespace App\Form;

use App\Entity\ApplicationJob;
use App\Entity\Cv;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApplicationJobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cv', EntityType::class, [
                'class' => Cv::class,
                'choices' => $options['available_cvs'],
                'choice_label' => 'title',
                'placeholder' => 'Select your CV',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-select']
            ]);
          
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationJob::class,
            'available_cvs' => [],
        ]);
    }
}