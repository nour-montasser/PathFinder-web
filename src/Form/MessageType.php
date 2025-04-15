<?php

namespace App\Form;

use App\Entity\Message;
use App\Entity\App_user;
use App\Entity\Channel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder->add('content', null, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Message cannot be empty.',
                ]),
            ]
        ]);
        
        // Only add mediaFile field if allowed
        if ($options['allow_attachment'] ?? true) {
            $builder->add('mediaFile', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'application/pdf'],
                        'mimeTypesMessage' => 'Only JPEG, PNG or PDF files allowed',
                    ])
                ]
            ]);
        }
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'allow_attachment' => true // Default to true
        ]);
    }
}