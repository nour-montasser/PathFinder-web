<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Certificates
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_certificate;

    #[ORM\Column(type: "bigint")]
    private string $id_cv;

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

    public function getId_certificate()
    {
        return $this->id_certificate;
    }

    public function setId_certificate($value)
    {
        $this->id_certificate = $value;
    }

    public function getId_cv()
    {
        return $this->id_cv;
    }

    public function setId_cv($value)
    {
        $this->id_cv = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia($value)
    {
        $this->media = $value;
    }

    public function getIssue_date()
    {
        return $this->issue_date;
    }

    public function setIssue_date($value)
    {
        $this->issue_date = $value;
    }

    public function getIssued_by()
    {
        return $this->issued_by;
    }

    public function setIssued_by($value)
    {
        $this->issued_by = $value;
    }
}
