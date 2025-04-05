<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Languages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_language;

    #[ORM\ManyToOne(targetEntity: Cv::class, inversedBy: "languages")]
    #[ORM\JoinColumn(name: "id_cv", referencedColumnName: "id_cv", onDelete: "CASCADE")]
    private Cv $cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $language_name;

    #[ORM\Column(type: "string", length: 255)]
    private string $level;

    // Getters and Setters

    public function getId_language(): string
    {
        return $this->id_language;
    }

    public function setId_language(string $id_language): self
    {
        $this->id_language = $id_language;
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

    public function getLanguage_name(): string
    {
        return $this->language_name;
    }

    public function setLanguage_name(string $language_name): self
    {
        $this->language_name = $language_name;
        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;
        return $this;
    }
}
