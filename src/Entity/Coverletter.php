<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Application_job;

#[ORM\Entity]
class Coverletter
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private int $id_cover_letter;

    #[ORM\OneToOne(targetEntity: Application_job::class)]
    #[ORM\JoinColumn(name: "id_app", referencedColumnName: "application_id")]
    private Application_job $application;

    #[ORM\Column(type: "text", length: 5000)]
    private string $content;

    #[ORM\Column(type: "string", length: 255)]
    private string $subject;

    public function getId_cover_letter(): int
    {
        return $this->id_cover_letter;
    }

    public function setId_cover_letter(int $id_cover_letter): self
    {
        $this->id_cover_letter = $id_cover_letter;
        return $this;
    }

    public function getApplication(): Application_job
    {
        return $this->application;
    }

    public function setApplication(Application_job $application): self
    {
        $this->application = $application;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
}