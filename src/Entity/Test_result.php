<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Test_result
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $id_result;  // Change to integer or big integer

    #[ORM\Column(type: "bigint")]
    private int $id_user;  // Change to integer

    #[ORM\Column(type: "bigint")]
    private int $id_test;  // Change to integer

    #[ORM\Column(type: "float")]
    private float $result;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "boolean")]
    private bool $status;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "testResults")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
    private App_user $user;
    

    #[ORM\ManyToOne(targetEntity: Skilltest::class)]
    #[ORM\JoinColumn(name: "id_test", referencedColumnName: "id_test")]
    private Skilltest $test;

    public function getId_result(): int
    {
        return $this->id_result;
    }

    public function setId_result(int $value): void
    {
        $this->id_result = $value;
    }

    public function getId_user(): int
    {
        return $this->id_user;
    }

    public function setId_user(int $value): void
    {
        $this->id_user = $value;
    }

    public function getId_test(): int
    {
        return $this->id_test;
    }

    public function setId_test(int $value): void
    {
        $this->id_test = $value;
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

    // Getter for user relationship
    public function getUser(): App_user
    {
        return $this->user;
    }

    // Setter for user relationship
    public function setUser(App_user $user): void
    {
        $this->user = $user;
    }

    // Getter for test relationship
    public function getTest(): Skilltest
    {
        return $this->test;
    }

    // Setter for test relationship
    public function setTest(Skilltest $test): void
    {
        $this->test = $test;
    }
}