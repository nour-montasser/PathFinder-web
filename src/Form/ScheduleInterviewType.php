<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleInterviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scheduleNow', CheckboxType::class, [
                'label' => 'Start Meeting Now',
                'required' => false,
                'data' => true,
            ])
            ->add('interviewDate', DateType::class, [
                'label' => 'Interview Date',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
            ])
            ->add('interviewTime', TimeType::class, [
                'label' => 'Interview Time',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}