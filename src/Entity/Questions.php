<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Skilltest;

#[ORM\Entity]
class Questions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_question = null;
    #[Assert\NotBlank(message: "Question cannot be blank.")]
    #[Assert\Length(min: 5, max: 255, minMessage: "Question is too short.")]
    private string $question;

    #[ORM\ManyToOne(targetEntity: Skilltest::class, inversedBy: "questions")]
    #[ORM\JoinColumn(name: "id_test", referencedColumnName: "id_test", onDelete: "CASCADE")]
    private Skilltest $skillTest;

    #[Assert\NotBlank(message: "Please provide possible responses.")]
    #[Assert\Regex(
        pattern: "/^[^,]+(,[^,]+)+$/",
        message: "Responses must be comma-separated (at least two).")]
    private string $responses;

    #[Assert\NotBlank(message: "You must specify the correct response.")]
    private string $correctResponse;

    #[Assert\NotNull]
    #[Assert\PositiveOrZero(message: "Score must be 0 or more.")]
    private int $score;

    public function getIdQuestion(): ?int
    {
        return $this->id_question;
    }
    public function getId(): ?int
    {
        return $this->id_question;
    }


    public function setIdQuestion(string $value): void
    {
        $this->id_question = $value;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }



    public function setQuestion(string $value): void
    {
        $this->question = $value;
    }

    public function getSkillTest(): Skilltest
    {
        return $this->skillTest;
    }

    public function setSkillTest(Skilltest $skillTest): self
    {
        $this->skillTest = $skillTest;
        return $this;
    }

    public function getResponses(): string
    {
        return $this->responses;
    }

    public function setResponses(string $value): void
    {
        $this->responses = $value;
    }

    public function getCorrectResponse(): string
    {
        return $this->correctResponse;
    }

    public function setCorrectResponse(string $value): void
    {
        $this->correctResponse = $value;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $value): void
    {
        $this->score = $value;
    }
}
