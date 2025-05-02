<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Test_result
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $id_result;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: "Result must be between 0 and 100.")]
    private float $result;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "Date cannot be null.")]
    #[Assert\Type("\DateTimeInterface")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'boolean')]
    #[Assert\NotNull]
    private bool $status;

    #[ORM\ManyToOne(targetEntity: App_user::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", onDelete: "CASCADE", nullable: false)]
    #[Assert\NotNull(message: "Result must be assigned to a user.")]
    private App_user $user;
    

    #[ORM\ManyToOne(targetEntity: Skilltest::class)]
    #[ORM\JoinColumn(name: "id_test", referencedColumnName: "id_test", onDelete: "CASCADE", nullable: false)]
    #[Assert\NotNull(message: "Result must be linked to a test.")]
    private Skilltest $test;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rating = null;

    // Getters and setters...

    public function getIdResult(): int
    {
        return $this->id_result;
    }

    public function getResult(): float
    {
        return $this->result;
    }

    public function setResult(float $value): void
    {
        $this->result = $value;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $value): void
    {
        $this->date = $value;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $value): void
    {
        $this->status = $value;
    }

    public function getUser(): App_user
    {
        return $this->user;
    }

    public function setUser(App_user $user): void
    {
        $this->user = $user;
    }

    public function getTest(): Skilltest
    {
        return $this->test;
    }

    public function setTest(Skilltest $test): void
    {
        $this->test = $test;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): void
    {
        $this->rating = $rating;
    }
}
