<?php

namespace App\Form;

use App\Entity\App_user;

use App\Entity\Channel;
use App\Entity\Message;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('media')
            ->add('sender', EntityType::class, [
                'class' => App_user::class,
'choice_label' => 'id',
            ])
            ->add('channel', EntityType::class, [
                'class' => Channel::class,
                'choice_label' => 'id',
                'data' => $options['data']->getChannel() // Set the pre-selected channel
            ])
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}