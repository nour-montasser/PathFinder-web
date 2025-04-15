<?php

namespace App\Form;

use App\Entity\Cv;
use App\Entity\Languages;
use App\Entity\Experience;
use App\Entity\Certificates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_title', TextType::class, ['label' => 'CV Title'])
            ->add('introduction', TextareaType::class, ['label' => 'Introduction'])
            ->add('skills', TextType::class, [
                'required' => true,  // Make skills required
                'label' => 'Skills (comma-separated)',
                'attr' => [
                    'placeholder' => 'e.g., HTML, CSS, JavaScript',
                    'maxlength' => 255
                ]
            ])
            ->add('languages', CollectionType::class, [
                'entry_type' => LanguagesType::class,  // A form type for Languages
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('experiences', CollectionType::class, [
                'entry_type' => ExperienceType::class,  // A form type for Experiences
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('certificates', CollectionType::class, [
                'entry_type' => CertificatesType::class,  // A form type for Certificates
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cv::class,
        ]);
    }
}
