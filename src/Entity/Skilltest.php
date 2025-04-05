<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;

use App\Entity\JobOffer;
use App\Entity\Questions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Skilltest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id_test = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "The title must not be empty.")]
    #[Assert\Length(min: 5, max: 255, minMessage: "Title is too short.", maxMessage: "Title can't be longer than 255 characters.")]

    private string $title;

    #[ORM\Column(type: "string", length: 1000)]
    #[Assert\NotBlank(message: "Please provide a description.")]
    #[Assert\Length(min: 10, max: 1000, minMessage: "Description is too short.")]
    private string $description;

    #[ORM\Column(type: "bigint")]
    #[Assert\NotNull(message: "Duration is required.")]
    #[Assert\Positive(message: "Duration must be a positive number.")]
    #[Assert\LessThanOrEqual(value: 180, message: "Tests can't be longer than 180 minutes.")]
    private int $duration;

    #[ORM\ManyToOne(targetEntity: JobOffer::class, inversedBy: "skillTests")]
    #[ORM\JoinColumn(name: "id_job_offer", referencedColumnName: "id_offer")]
    #[Assert\NotNull(message: "You must assign a Job Offer to this Skilltest.")]
    private JobOffer $jobOffer;

    #[ORM\Column(type: "bigint")]
    #[Assert\NotNull(message: "Score required is mandatory.")]
    #[Assert\GreaterThanOrEqual(value: 1, message: "Required score must be at least 1.")]
    private int $score_required;
    #[ORM\OneToMany(mappedBy: "skillTest", targetEntity: Questions::class)]
    private Collection $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getIdTest(): ?int
    {
        return $this->id_test;
    }
    public function getId(): ?int
    {
        return $this->id_test;
    }


    public function setIdTest(int $value): void
    {
        $this->id_test = $value;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): void
    {
        $this->title = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $value): void
    {
        $this->description = $value;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $value): void
    {
        $this->duration = $value;
    }

    public function getJobOffer(): JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): void
    {
        $this->jobOffer = $jobOffer;
    }

    public function getScoreRequired(): int
    {
        return $this->score_required;
    }

    public function setScoreRequired(int $value): void
    {
        $this->score_required = $value;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }
    public function addQuestion(Questions $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setSkillTest($this); // make sure the relation is set both ways
        }

        return $this;
    }

    public function removeQuestion(Questions $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getSkillTest() === $this) {
                $question->setSkillTest(null);
            }
        }

        return $this;
    }


}
