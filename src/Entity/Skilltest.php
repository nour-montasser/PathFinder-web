<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Job_offer;
use App\Entity\Questions;

#[ORM\Entity]
class Skilltest
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private int $id_test;  // Change to integer

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 1000)]
    private string $description;

    #[ORM\Column(type: "bigint")]
    private int $duration;  // Change to integer

    #[ORM\ManyToOne(targetEntity: Job_offer::class, inversedBy: "skillTests")]
    #[ORM\JoinColumn(name: "id_job_offer", referencedColumnName: "id_offer")]
    private Job_offer $jobOffer;

    #[ORM\Column(type: "bigint")]
    private int $score_required;  // Change to integer
        // NEW: Add the inverse side for Questions
        #[ORM\OneToMany(mappedBy: "skillTest", targetEntity: Questions::class)]
        private Collection $questions;
        public function __construct()
        {
            $this->questions = new ArrayCollection();
        }    

        public function getQuestions(): Collection
        {
            return $this->questions;
        }
    
        public function addQuestion(Questions $question): self
        {
            if (!$this->questions->contains($question)) {
                $this->questions[] = $question;
                $question->setSkillTest($this);
            }
            return $this;
        }
    
        public function removeQuestion(Questions $question): self
        {
            if ($this->questions->removeElement($question)) {
                if ($question->getSkillTest() === $this) {
                    $question->setSkillTest(null);
                }
            }
            return $this;
        }
    public function getId_test(): int
    {
        return $this->id_test;
    }

    public function setId_test(int $value): void
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

    public function getJobOffer(): Job_Offer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?Job_Offer $jobOffer): void
    {
        $this->jobOffer = $jobOffer;
    }

    public function getScore_required(): int
    {
        return $this->score_required;
    }

    public function setScore_required(int $value): void
    {
        $this->score_required = $value;
    }
}
