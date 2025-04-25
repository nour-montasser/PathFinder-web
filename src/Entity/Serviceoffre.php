<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Applicationservice;
use App\Entity\App_user;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
class Serviceoffre
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(type: "bigint")]
    private ?int $idService= null;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "serviceOffers")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private ?App_user $user = null;
    

    #[ORM\Column(type: "text")]

    private string $description;

    #[ORM\Column(type: "string", length: 25)]
    #[Assert\NotBlank(message: "a titre is required.")]
    #[Assert\Length(
    min: 5,
    max: 25,
    minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
    maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
)]
    private string $title;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_posted;

    #[ORM\Column(type: "string", length: 25)]
    private string $field;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
private $priceEstimation;


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




    #[ORM\OneToMany(mappedBy: "service", targetEntity: Applicationservice::class)]
    private Collection $applicationservices;

    public function __construct()
    {
        $this->applicationservices = new ArrayCollection();
    }

    
    public function getIdService(): ?int
    {
        return $this->idService;
    }

    public function getUser(): ?App_user { return $this->user; }

public function setUser(?App_user $user): self { $this->user = $user; return $this; }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
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

    public function getDatePosted(): \DateTimeInterface
    {
        return $this->date_posted;
    }

    public function setDatePosted(\DateTimeInterface $date_posted): self
    {
        $this->date_posted = $date_posted;
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getRequiredEducation(): string
{
    return $this->required_education;
}


public function setRequiredEducation(string $required_education): self
{
    $this->required_education = $required_education;
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

    public function getExperienceLevel(): string
    {
        return $this->experience_level;
    }

    public function setExperienceLevel(string $experience_level): self
    {
        $this->experience_level = $experience_level;
        return $this;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPriceEstimation(): ?string
{
    return $this->priceEstimation;
}

public function setPriceEstimation(?string $priceEstimation): self
{
    $this->priceEstimation = $priceEstimation;

    return $this;
}
   


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