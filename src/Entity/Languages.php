<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Languages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private int $id_language;

    #[ORM\ManyToOne(targetEntity: Cv::class, inversedBy: "languages")]
    #[ORM\JoinColumn(name: "id_cv", referencedColumnName: "id_cv", onDelete: "CASCADE")]
    private Cv $cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $language_name;

    #[ORM\Column(type: "string", length: 255)]
    private string $level;

    // Getters and Setters

    public function getIdLanguage(): string
    {
        return $this->id_language;
    }

    public function setIdLanguage(int $id_language): self
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

    public function getLanguageName(): string
    {
        return $this->language_name;
    }

    public function setLanguageName(string $language_name): self
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