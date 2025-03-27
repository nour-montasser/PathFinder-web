<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Skilltest
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_test;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 1000)]
    private string $description;

    #[ORM\Column(type: "bigint")]
    private string $duration;

    #[ORM\Column(type: "bigint")]
    private string $id_job_offer;

    #[ORM\Column(type: "bigint")]
    private string $score_required;

    public function getId_test()
    {
        return $this->id_test;
    }

    public function setId_test($value)
    {
        $this->id_test = $value;
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

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($value)
    {
        $this->duration = $value;
    }

    public function getId_job_offer()
    {
        return $this->id_job_offer;
    }

    public function setId_job_offer($value)
    {
        $this->id_job_offer = $value;
    }

    public function getScore_required()
    {
        return $this->score_required;
    }

    public function setScore_required($value)
    {
        $this->score_required = $value;
    }
}
