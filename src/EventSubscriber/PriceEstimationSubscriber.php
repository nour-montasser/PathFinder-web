<?php
namespace App\EventSubscriber;

use App\Service\AIDescriptionGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Psr\Log\LoggerInterface;

class PriceEstimationSubscriber implements EventSubscriberInterface
{
    private $aiDescriptionGenerator;
    private $logger;

    public function __construct(AIDescriptionGenerator $aiDescriptionGenerator, LoggerInterface $logger)
    {
        $this->aiDescriptionGenerator = $aiDescriptionGenerator;
        $this->logger = $logger;
    }
    

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onFormSubmit',
        ];
    }

    public function onFormSubmit(FormEvent $event)
{
    $form = $event->getForm();
    $data = $event->getData();

    // Debug logging
    $this->logger->debug('Price Estimation Subscriber Triggered', [
        'has_title' => isset($data['title']),
        'has_description' => isset($data['description']),
        'has_field' => isset($data['field']),
        'has_experience' => isset($data['experience_level']),
    ]);

    // Only proceed if all required fields have values
    if (!empty($data['title']) && 
        !empty($data['description']) && 
        !empty($data['field']) && 
        !empty($data['experience_level'])) {
        
        try {
            $price = $this->aiDescriptionGenerator->generatePriceEstimation(
                $data['title'],
                $data['description'],
                $data['field'],
                $data['experience_level']
            );

            // Ensure we got a valid numeric value
            if (is_numeric($price)) {
                $roundedPrice = number_format((float)$price, 2, '.', '');
                $data['price_estimation'] = $roundedPrice;
                $event->setData($data);
            
                if ($form->has('price_estimation')) {
                    $form->get('price_estimation')->setData($roundedPrice);
                }
            
                $this->logger->info('✅ AI Price Estimation Set', ['price' => $roundedPrice]);
            } else {
                $this->logger->warning('⚠️ AI returned invalid price', ['value' => $price]);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Price estimation failed', ['error' => $e->getMessage()]);
        }
    }
}
    
}
