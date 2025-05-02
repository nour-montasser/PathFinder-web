<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Applicationservice::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(name: "application_id", referencedColumnName: "id_app", nullable: false)]
    private ?Applicationservice $application = null;

    #[ORM\ManyToOne(targetEntity: App_user::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id_user", nullable: false)]
    private ?App_user $user = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column(length: 255)]
    private ?string $receiptUrl = null;

    #[ORM\Column(name: "stripe_session_id", type: "string", length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    // Getters & Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): ?Applicationservice
    {
        return $this->application;
    }

    public function setApplication(?Applicationservice $application): self
    {
        $this->application = $application;
        return $this;
    }

    public function getUser(): ?App_user
    {
        return $this->user;
    }

    public function setUser(?App_user $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeInterface $paidAt): self
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    public function getReceiptUrl(): ?string
    {
        return $this->receiptUrl;
    }

    public function setReceiptUrl(string $receiptUrl): self
    {
        $this->receiptUrl = $receiptUrl;
        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;
        return $this;
    }
}
