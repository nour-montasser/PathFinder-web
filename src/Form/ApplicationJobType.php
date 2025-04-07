<?php
namespace App\Form;

use App\Entity\Cv;
use App\Entity\ApplicationJob;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;  

class ApplicationJobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cv', EntityType::class, [
            'class' => Cv::class,
            'choices' => $options['available_cvs'],
            'choice_label' => 'title',
            'placeholder' => 'Select your CV',
            'attr' => [
                'class' => 'form-select'
            ],
            'label' => 'Choose a CV to submit'
        ]);
       
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ApplicationJob::class,
            'available_cvs' => [], // Custom option for filtered CVs
        ]);
    }
}