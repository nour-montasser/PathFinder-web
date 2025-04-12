<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Job_offer;
use App\Entity\App_user;
use App\Entity\Cv;
use App\Repository\ApplicationJobRepository;

#[ORM\Entity(repositoryClass: ApplicationJobRepository::class)]
#[ORM\Table(name: "application_job")] // 👈 garde le nom SQL que tu veux
class ApplicationJob
{
    #[ORM\Id]   
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $application_id;

    #[ORM\ManyToOne(targetEntity: Job_offer::class, inversedBy: "applications")]
    #[ORM\JoinColumn(name: "job_offer_id", referencedColumnName: "id_offer")]
    private Job_offer $jobOffer;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "jobApplications")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
    private App_user $user;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_application;

    #[ORM\Column(type: "string", length: 50)]
    private string $status;

    #[ORM\ManyToOne(targetEntity: Cv::class, inversedBy: "applications")]
    #[ORM\JoinColumn(name: "cv_id", referencedColumnName: "id_cv")]
    private Cv $cv;
    

    public function getApplication_id(): string
    {
        return $this->application_id;
    }

    public function setApplication_id(int $value): self
    {
        $this->application_id = $value;
        return $this;
    }

    public function getJobOffer(): Job_offer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?Job_offer $jobOffer): self
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

    public function getDateApplication(): \DateTimeInterface
    {
        return $this->date_application;
    }

    public function setDateApplication(\DateTimeInterface $value): self
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

    public function isDraft(): bool
{
    return str_starts_with($this->status, 'Applying-');
}

// In ApplicationJob.php
public function getStatusStep(): ?int
{
    if (str_starts_with($this->status, 'Applying-')) {
        return (int) explode('-', $this->status)[1];
    }
    return null;
}

public function isInProgress(): bool
{
    return str_starts_with($this->status, 'Applying-');
}

public function isPending(): bool
{
    return $this->status === 'Pending';
}

}
