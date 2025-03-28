<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToOne(targetEntity: Job_Offer::class, inversedBy: "skilltests")]
    #[ORM\JoinColumn(name: "id_job_offer", referencedColumnName: "id_offer")]
    private Job_Offer $jobOffer;  // Define relationship explicitly

    #[ORM\Column(type: "bigint")]
    private int $score_required;  // Change to integer

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
