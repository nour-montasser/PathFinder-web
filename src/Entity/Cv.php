<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\App_user;
use App\Entity\Certificates;
use App\Entity\Experience;
use App\Entity\Languages;
use App\Entity\Application_job;

#[ORM\Entity]
class Cv
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private int $id_cv;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "cvs")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $user;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_title;

    #[ORM\Column(type: "string", length: 500)]
    private string $introduction;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_creation;

    #[ORM\Column(type: "string", length: 255)]
    private string $skills;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $last_viewed;

    #[ORM\Column(type: "boolean")]
    private bool $favorite;

    #[ORM\OneToMany(mappedBy: "cv", targetEntity: Certificates::class, cascade: ["persist", "remove"])]
    private Collection $certificates;

    #[ORM\OneToMany(mappedBy: "cv", targetEntity: Experience::class, cascade: ["persist", "remove"])]
    private Collection $experiences;

    #[ORM\OneToMany(mappedBy: "cv", targetEntity: Languages::class, cascade: ["persist", "remove"])]
    private Collection $languages;

    #[ORM\OneToMany(mappedBy: "cv", targetEntity: Application_job::class)]
    private Collection $applications;

    public function __construct()
    {
        $this->certificates = new ArrayCollection();
        $this->experiences = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }

    public function getId_cv(): int
    {
        return $this->id_cv;
    }

    public function setId_cv(int $id_cv): self
    {
        $this->id_cv = $id_cv;
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

    public function getUser_title(): string
    {
        return $this->user_title;
    }

    public function setUser_title(string $user_title): self
    {
        $this->user_title = $user_title;
        return $this;
    }

    public function getIntroduction(): string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;
        return $this;
    }

    public function getDate_creation(): \DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
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

    public function getLast_viewed(): \DateTimeInterface
    {
        return $this->last_viewed;
    }

    public function setLast_viewed(\DateTimeInterface $last_viewed): self
    {
        $this->last_viewed = $last_viewed;
        return $this;
    }

    public function getFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): self
    {
        $this->favorite = $favorite;
        return $this;
    }

    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificates $certificate): self
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates[] = $certificate;
            $certificate->setCv($this);
        }
        return $this;
    }

    public function removeCertificate(Certificates $certificate): self
    {
        if ($this->certificates->removeElement($certificate)) {
            if ($certificate->getCv() === $this) {
                $certificate->setCv(null);
            }
        }
        return $this;
    }

    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->setCv($this);
        }
        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->removeElement($experience)) {
            if ($experience->getCv() === $this) {
                $experience->setCv(null);
            }
        }
        return $this;
    }

    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Languages $language): self
    {
        if (!$this->languages->contains($language)) {
            $this->languages[] = $language;
            $language->setCv($this);
        }
        return $this;
    }

    public function removeLanguage(Languages $language): self
    {
        if ($this->languages->removeElement($language)) {
            if ($language->getCv() === $this) {
                $language->setCv(null);
            }
        }
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
            $application->setCv($this);
        }
        return $this;
    }

    public function removeApplication(Application_job $application): self
    {
        if ($this->applications->removeElement($application)) {
            if ($application->getCv() === $this) {
                $application->setCv(null);
            }
        }
        return $this;
    }
}