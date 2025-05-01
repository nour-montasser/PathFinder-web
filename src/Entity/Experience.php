<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Cv;

#[ORM\Entity]
class Experience
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private int $id_experience;

    #[ORM\ManyToOne(targetEntity: Cv::class, inversedBy: "experiences")]
    #[ORM\JoinColumn(name: "id_cv", referencedColumnName: "id_cv", onDelete: "CASCADE")]
    private Cv $cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $type;

    #[ORM\Column(type: "string", length: 255)]
    private string $position;

    #[ORM\Column(type: "string", length: 255)]
    private string $location_name;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $start_date;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $end_date;

    #[ORM\Column(type: "string", length: 500)]
    private string $description;

    public function getIdExperience(): int
    {
        return $this->id_experience;
    }

    public function setIdExperience(int $id_experience): self
    {
        $this->id_experience = $id_experience;
        return $this;
    }

    public function getCv(): Cv
    {
        return $this->cv;
    }

    public function setCv(?Cv $cv): self
    {
        $this->cv = $cv;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getLocationName(): string
    {
        return $this->location_name;
    }

    public function setLocationName(string $location_name): self
    {
        $this->location_name = $location_name;
        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): self
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}