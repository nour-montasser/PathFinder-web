<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Languages
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_language;

    #[ORM\Column(type: "bigint")]
    private string $id_cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $language_name;

    #[ORM\Column(type: "string", length: 255)]
    private string $level;

    public function getId_language()
    {
        return $this->id_language;
    }

    public function setId_language($value)
    {
        $this->id_language = $value;
    }

    public function getId_cv()
    {
        return $this->id_cv;
    }

    public function setId_cv($value)
    {
        $this->id_cv = $value;
    }

    public function getLanguage_name()
    {
        return $this->language_name;
    }

    public function setLanguage_name($value)
    {
        $this->language_name = $value;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($value)
    {
        $this->level = $value;
    }
}
