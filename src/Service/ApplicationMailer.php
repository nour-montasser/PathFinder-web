<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Entity\ApplicationJob;

class ApplicationMailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendApplicationStatusEmail(
        ApplicationJob $application, 
        string $status,
        ?string $meetLink = null,
        ?\DateTimeInterface $interviewTime = null
    ): void {
        $subject = match($status) {
            'Accepted' => $meetLink ? 'Interview Scheduled' : 'Application Accepted',
            'Rejected' => 'Application Update',
            default => 'Application Update'
        };
    
        $email = (new TemplatedEmail())
            ->from(new Address('nourmo49@gmail.com', 'PathFinder'))
            ->to(new Address($application->getUser()->getEmail(), $application->getUser()->getName()))
            ->subject(sprintf(
                '%s: %s at %s', 
                $subject,
                $application->getJobOffer()->getTitle(),
                $application->getJobOffer()->getUser()->getName()
            ))
            ->htmlTemplate('application_job/application_email_status.html.twig')
            ->context([
                'applicantName' => $application->getUser()->getName(),
                'jobTitle' => $application->getJobOffer()->getTitle(),
                'companyName' => $application->getJobOffer()->getUser()->getName(),
                'status' => $status,
                'meetLink' => $meetLink,
                'interviewTime' => $interviewTime,
                'companyWebsite' => 'https://pathfinder.com',
                'privacyPolicyUrl' => 'https://pathfinder/privacy',
                'unsubscribeUrl' => 'https://pathfinder.com/unsubscribe'
            ]);
    
        $this->mailer->send($email);
    }
    

    private function getEmailHtmlContent(ApplicationJob $application, string $status): string
    {
        $jobTitle = $application->getJobOffer()->getTitle();
        $companyName = $application->getJobOffer()->getUser()->getName();
        
        return sprintf('
            <h1>Application Update</h1>
            <p>Dear %s,</p>
            <p>Your application for the position <strong>%s</strong> at <strong>%s</strong> has been <strong>%s</strong>.</p>
            %s
            <p>Best regards,<br>The %s Team</p>',
            $application->getUser()->getName(),
            $jobTitle,
            $companyName,
            $status,
            $status === 'Accepted' ? 
                '<p>We will contact you shortly to discuss the next steps.</p>' : 
                '<p>Thank you for your interest in our company.</p>',
            $companyName
        );
    }
}