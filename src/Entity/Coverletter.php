<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\ApplicationJob;

#[ORM\Entity]
class Coverletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $id_cover_letter;

    #[ORM\OneToOne(inversedBy: "coverletter", targetEntity: ApplicationJob::class)]
    #[ORM\JoinColumn(name: "id_app", referencedColumnName: "application_id", onDelete: "CASCADE")]
    private ?ApplicationJob $application;
    

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

    public function getApplication(): ApplicationJob
    {
        return $this->application;
    }

    public function setApplication(?ApplicationJob $application): self
{
    $this->application = $application;
    if ($application !== null && $application->getCoverletter() !== $this) {
        $application->setCoverletter($this);
    }
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


     public function __construct(ApplicationJob $application = null)
    {
        if ($application !== null) {
            $this->application = $application;
            $application->setCoverletter($this);
        }
    }
}