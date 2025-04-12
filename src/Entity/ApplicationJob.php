<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\JobOffer;
use App\Entity\App_user;
use App\Entity\Cv;

#[ORM\Entity]
class ApplicationJob
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private int $application_id;

    #[ORM\ManyToOne(targetEntity: JobOffer::class, inversedBy: "applications")]
    #[ORM\JoinColumn(name: "JobOffer_id", referencedColumnName: "id_offer")]
    private JobOffer $jobOffer;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "jobApplications")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
    private App_user $user;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_application;

    #[ORM\Column(type: "string", length: 50)]
    private string $status;

    #[ORM\ManyToOne(targetEntity: Cv::class)]
    #[ORM\JoinColumn(name: "cv_id", referencedColumnName: "id_cv")]
    private Cv $cv;

    public function getApplication_id(): string
    {
        return $this->application_id;
    }

    public function setApplication_id(string $value): self
    {
        $this->application_id = $value;
        return $this;
    }

    public function getJobOffer(): JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): self
    {
        $this->jobOffer = $jobOffer;
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

    public function getDate_application(): \DateTimeInterface
    {
        return $this->date_application;
    }

    public function setDate_application(\DateTimeInterface $value): self
    {
        $this->date_application = $value;
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

    public function getCv(): Cv
    {
        return $this->cv;
    }

    public function setCv(?Cv $cv): self
    {
        $this->cv = $cv;
        return $this;
    }
}