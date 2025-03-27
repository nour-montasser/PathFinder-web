<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Applicationservice;

#[ORM\Entity]
class Serviceoffre
{

    #[ORM\Id]
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

    public function getId_service()
    {
        return $this->id_service;
    }

    public function setId_service($value)
    {
        $this->id_service = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getDate_posted()
    {
        return $this->date_posted;
    }

    public function setDate_posted($value)
    {
        $this->date_posted = $value;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField($value)
    {
        $this->field = $value;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($value)
    {
        $this->price = $value;
    }

    public function getRequired_education()
    {
        return $this->required_education;
    }

    public function setRequired_education($value)
    {
        $this->required_education = $value;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function setSkills($value)
    {
        $this->skills = $value;
    }

    public function getExperience_level()
    {
        return $this->experience_level;
    }

    public function setExperience_level($value)
    {
        $this->experience_level = $value;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($value)
    {
        $this->duration = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_service", targetEntity: Applicationservice::class)]
    private Collection $applicationservices;

        public function getApplicationservices(): Collection
        {
            return $this->applicationservices;
        }
    
        public function addApplicationservice(Applicationservice $applicationservice): self
        {
            if (!$this->applicationservices->contains($applicationservice)) {
                $this->applicationservices[] = $applicationservice;
                $applicationservice->setId_service($this);
            }
    
            return $this;
        }
    
        public function removeApplicationservice(Applicationservice $applicationservice): self
        {
            if ($this->applicationservices->removeElement($applicationservice)) {
                // set the owning side to null (unless already changed)
                if ($applicationservice->getId_service() === $this) {
                    $applicationservice->setId_service(null);
                }
            }
    
            return $this;
        }
}
