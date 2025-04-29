<?php

namespace App\Service;

use App\Entity\App_user;
use App\Entity\Feedback;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class FeedbackMailer
{
    private MailerInterface $mailer;
    private const ADMIN_EMAIL = 'nourmo49@gmail.com';
    private const ADMIN_NAME = 'PathFinder Admin';

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendThankYouEmail(App_user $user): void
    {
        $email = (new Email())
            ->from(new Address(self::ADMIN_EMAIL, self::ADMIN_NAME))
            ->to(new Address($user->getEmail(), $user->getName()))
            ->subject('Thank you for your feedback!')
            ->html(
                '<p>Dear ' . htmlspecialchars($user->getName()) . ',</p>
                 <p>Thank you for your valuable feedback! We appreciate your time and contribution.</p>
                 <p>— The PathFinder Team</p>'
            );

        $this->mailer->send($email);
    }

    public function sendAdminNotification(App_user $user, Feedback $feedback): void
    {
        $email = (new Email())
            ->from(new Address(self::ADMIN_EMAIL, self::ADMIN_NAME))
            ->to(new Address(self::ADMIN_EMAIL, self::ADMIN_NAME))
            ->subject('New Feedback Received from ' . $user->getName())
            ->html(
                '<p><strong>New Feedback Details:</strong></p>
                 <p><strong>From:</strong> ' . htmlspecialchars($user->getName()) . ' (' . htmlspecialchars($user->getEmail()) . ')</p>
                 <p><strong>Subject:</strong> ' . htmlspecialchars($feedback->getSubject()) . '</p>
                 <p><strong>Message:</strong></p>
                 <p>' . nl2br(htmlspecialchars($feedback->getMessage())) . '</p>'
            );

        $this->mailer->send($email);
    }
}