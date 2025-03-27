<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Coverletter
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_CoverLetter;

    #[ORM\Column(type: "bigint")]
    private string $id_app;

    #[ORM\Column(type: "string", length: 5000)]
    private string $content;

    #[ORM\Column(type: "string", length: 255)]
    private string $subject;

    public function getId_CoverLetter()
    {
        return $this->id_CoverLetter;
    }

    public function setId_CoverLetter($value)
    {
        $this->id_CoverLetter = $value;
    }

    public function getId_app()
    {
        return $this->id_app;
    }

    public function setId_app($value)
    {
        $this->id_app = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($value)
    {
        $this->subject = $value;
    }
}
