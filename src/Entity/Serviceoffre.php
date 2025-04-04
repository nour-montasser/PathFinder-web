<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Serviceoffre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_service;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 25)]
    private string $title;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_posted;

    #[ORM\Column(type: "string", length: 25)]
    private string $field;

    #[ORM\Column(type: "float")]
    private float $price;

    #[ORM\Column(type: "string", length: 100)]
    private string $required_education;

    #[ORM\Column(type: "string", length: 100)]
    private string $skills;

    #[ORM\Column(type: "string", length: 50)]
    private string $experience_level;

    #[ORM\Column(type: "string", length: 50)]
    private string $duration;

    #[ORM\Column(type: "string", length: 20)]
    private string $status;

    // OneToMany relationship with Applicationservice
    #[ORM\OneToMany(mappedBy: "service", targetEntity: Applicationservice::class)]
    private Collection $applicationservices;

    public function __construct()
    {
        $this->applicationservices = new ArrayCollection();
    }

    // Getters and setters
    public function getId_service(): string
    {
        return $this->id_service;
    }

    public function setId_service(string $value): self
    {
        $this->id_service = $value;
        return $this;
    }

    public function getid_user(): string
    {
        return $this->id_user;
    }

    public function setid_user(string $value): self
    {
        $this->id_user = $value;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $value): self
    {
        $this->description = $value;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $value): self
    {
        $this->title = $value;
        return $this;
    }

    public function getDate_posted(): \DateTimeInterface
    {
        return $this->date_posted;
    }

    public function setDate_posted(\DateTimeInterface $value): self
    {
        $this->date_posted = $value;
        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $value): self
    {
        $this->field = $value;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $value): self
    {
        $this->price = $value;
        return $this;
    }

    public function getRequired_education(): string
    {
        return $this->required_education;
    }

    public function setRequired_education(string $value): self
    {
        $this->required_education = $value;
        return $this;
    }

    public function getSkills(): string
    {
        return $this->skills;
    }

    public function setSkills(string $value): self
    {
        $this->skills = $value;
        return $this;
    }

    public function getExperience_level(): string
    {
        return $this->experience_level;
    }

    public function setExperience_level(string $value): self
    {
        $this->experience_level = $value;
        return $this;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $value): self
    {
        $this->duration = $value;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $value): self
    {
        $this->status = $value;
        return $this;
    }

    // Getters for related Applicationservice entities
    public function getApplicationservices(): Collection
    {
        return $this->applicationservices;
    }

    public function addApplicationservice(Applicationservice $applicationservice): self
    {
        if (!$this->applicationservices->contains($applicationservice)) {
            $this->applicationservices[] = $applicationservice;
            $applicationservice->setService($this);
        }

        return $this;
    }

    public function removeApplicationservice(Applicationservice $applicationservice): self
    {
        if ($this->applicationservices->removeElement($applicationservice)) {
            if ($applicationservice->getService() === $this) {
                $applicationservice->setService(null);
            }
        }

        return $this;
    }
}
