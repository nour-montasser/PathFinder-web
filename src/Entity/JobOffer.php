<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\AppUser;
use App\Entity\ApplicationJob;
use App\Entity\Skilltest;

#[ORM\Entity]
class JobOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $idOffer;

    #[ORM\ManyToOne(targetEntity: AppUser::class, inversedBy: "jobOffers")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private AppUser $user;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text", length: 1000)]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $datePosted;

    #[ORM\Column(type: "string", length: 255)]
    private string $type;

    #[ORM\Column(type: "integer")]
    private int $numberOfSpots;

    #[ORM\Column(type: "string", length: 255)]
    private string $requiredEducation;

    #[ORM\Column(type: "string", length: 255)]
    private string $requiredExperience;

    #[ORM\Column(type: "string", length: 255)]
    private string $skills;

    #[ORM\Column(type: "string", length: 255)]
    private string $field;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    #[ORM\OneToMany(mappedBy: "jobOffer", targetEntity: ApplicationJob::class)]
    private Collection $applications;

    #[ORM\OneToMany(mappedBy: "jobOffer", targetEntity: Skilltest::class)]
    private Collection $skillTests;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->skillTests = new ArrayCollection();
    }

    public function getidOffer(): int
    {
        return $this->idOffer;
    }

    public function setidOffer(int $idOffer): self
    {
        $this->idOffer = $idOffer;
        return $this;
    }

    public function getUser(): AppUser
    {
        return $this->user;
    }

    public function setUser(AppUser $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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

    public function getdatePosted(): \DateTimeInterface
    {
        return $this->datePosted;
    }

    public function setdatePosted(\DateTimeInterface $datePosted): self
    {
        $this->datePosted = $datePosted;
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

    public function getNumberOfSpots(): int
    {
        return $this->numberOfSpots;
    }

    public function setNumberOfSpots(int $numberOfSpots): self
    {
        $this->numberOfSpots = $numberOfSpots;
        return $this;
    }

    public function getRequiredEducation(): string
    {
        return $this->requiredEducation;
    }

    public function setRequiredEducation(string $requiredEducation): self
    {
        $this->requiredEducation = $requiredEducation;
        return $this;
    }

    public function getRequiredExperience(): string
    {
        return $this->requiredExperience;
    }

    public function setRequiredExperience(string $requiredExperience): self
    {
        $this->requiredExperience = $requiredExperience;
        return $this;
    }

    public function getSkills(): string
    {
        return $this->skills;
    }

    public function setSkills(string $skills): self
    {
        $this->skills = $skills;
        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(ApplicationJob $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setJobOffer($this);
        }
        return $this;
    }

    public function removeApplication(ApplicationJob $application): self
    {
        if ($this->applications->removeElement($application)) {
            if ($application->getJobOffer() === $this) {
                $application->setJobOffer(null);
            }
        }
        return $this;
    }

    public function getSkillTests(): Collection
    {
        return $this->skillTests;
    }

    public function addSkillTest(Skilltest $skillTest): self
    {
        if (!$this->skillTests->contains($skillTest)) {
            $this->skillTests[] = $skillTest;
            $skillTest->setJobOffer($this);
        }
        return $this;
    }

    public function removeSkillTest(Skilltest $skillTest): self
    {
        if ($this->skillTests->removeElement($skillTest)) {
            if ($skillTest->getJobOffer() === $this) {
                $skillTest->setJobOffer(null);
            }
        }
        return $this;
    }
}