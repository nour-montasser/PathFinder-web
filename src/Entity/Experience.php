<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Experience
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_experience;

    #[ORM\Column(type: "bigint")]
    private string $id_cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $TYPE;

    #[ORM\Column(type: "string", length: 255)]
    private string $POSITION;

    #[ORM\Column(type: "string", length: 255)]
    private string $location_name;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $start_date;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $end_date;

    #[ORM\Column(type: "string", length: 500)]
    private string $description;

    public function getId_experience()
    {
        return $this->id_experience;
    }

    public function setId_experience($value)
    {
        $this->id_experience = $value;
    }

    public function getId_cv()
    {
        return $this->id_cv;
    }

    public function setId_cv($value)
    {
        $this->id_cv = $value;
    }

    public function getTYPE()
    {
        return $this->TYPE;
    }

    public function setTYPE($value)
    {
        $this->TYPE = $value;
    }

    public function getPOSITION()
    {
        return $this->POSITION;
    }

    public function setPOSITION($value)
    {
        $this->POSITION = $value;
    }

    public function getLocation_name()
    {
        return $this->location_name;
    }

    public function setLocation_name($value)
    {
        $this->location_name = $value;
    }

    public function getStart_date()
    {
        return $this->start_date;
    }

    public function setStart_date($value)
    {
        $this->start_date = $value;
    }

    public function getEnd_date()
    {
        return $this->end_date;
    }

    public function setEnd_date($value)
    {
        $this->end_date = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }
}
