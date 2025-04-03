<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\App_user;
use App\Entity\Application_Job;
use App\Entity\Skilltest;

#[ORM\Entity]
class Job_offer
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private int $id_offer;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "jobOffers")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $user;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text", length: 1000)]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_posted;

    #[ORM\Column(type: "string", length: 255)]
    private string $type;

    #[ORM\Column(type: "integer")]
    private int $number_of_spots;

    #[ORM\Column(type: "string", length: 255)]
    private string $required_education;

    #[ORM\Column(type: "string", length: 255)]
    private string $required_experience;

    #[ORM\Column(type: "string", length: 255)]
    private string $skills;

    #[ORM\Column(type: "string", length: 255)]
    private string $field;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    #[ORM\OneToMany(mappedBy: "jobOffer", targetEntity: Application_job::class)]
    private Collection $applications;

    #[ORM\OneToMany(mappedBy: "jobOffer", targetEntity: Skilltest::class)]
    private Collection $skillTests;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->skillTests = new ArrayCollection();
    }

    public function getId_offer(): int
    {
        return $this->id_offer;
    }

    public function setId_offer(int $id_offer): self
    {
        $this->id_offer = $id_offer;
        return $this;
    }

    public function getUser(): App_user
    {
        return $this->user;
    }

    public function setUser(App_user $user): self
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

    public function getDate_posted(): \DateTimeInterface
    {
        return $this->date_posted;
    }

    public function setDate_posted(\DateTimeInterface $date_posted): self
    {
        $this->date_posted = $date_posted;
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

    public function getNumber_of_spots(): int
    {
        return $this->number_of_spots;
    }

    public function setNumber_of_spots(int $number_of_spots): self
    {
        $this->number_of_spots = $number_of_spots;
        return $this;
    }

    public function getRequired_education(): string
    {
        return $this->required_education;
    }

    public function setRequired_education(string $required_education): self
    {
        $this->required_education = $required_education;
        return $this;
    }

    public function getRequired_experience(): string
    {
        return $this->required_experience;
    }

    public function setRequired_experience(string $required_experience): self
    {
        $this->required_experience = $required_experience;
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

    public function addApplication(Application_job $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setJobOffer($this);
        }
        return $this;
    }

    public function removeApplication(Application_job $application): self
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