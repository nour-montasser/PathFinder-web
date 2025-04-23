<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Cv;

#[ORM\Entity]
class Certificates
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private int $id_certificate;

    #[ORM\ManyToOne(targetEntity: Cv::class, inversedBy: "certificates")]
    #[ORM\JoinColumn(name: "id_cv", referencedColumnName: "id_cv", onDelete: "CASCADE")]
    private Cv $cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 500)]
    private string $description;

    #[ORM\Column(type: "string", length: 255)]
    private string $media;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $issue_date;

    #[ORM\Column(type: "string", length: 255)]
    private string $issued_by;

    public function getIdCertificate(): int
    {
        return $this->id_certificate;
    }

    public function setIdCertificate(int $id_certificate): self
    {
        $this->id_certificate = $id_certificate;
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

    public function getMedia(): string
    {
        return $this->media;
    }

    public function setMedia(string $media): self
    {
        $this->media = $media;
        return $this;
    }

    public function getIssueDate(): \DateTimeInterface
    {
        return $this->issue_date;
    }

    public function setIssueDate(\DateTimeInterface $issue_date): self
    {
        $this->issue_date = $issue_date;
        return $this;
    }

    public function getIssuedBy(): string
    {
        return $this->issued_by;
    }

    public function setIssuedBy(string $issued_by): self
    {
        $this->issued_by = $issued_by;
        return $this;
    }
}