<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Report
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_report;

    #[ORM\Column(type: "bigint")]
    private string $id_user_sender;

    #[ORM\Column(type: "bigint")]
    private string $id_user_target;

    #[ORM\Column(type: "string", length: 500)]
    private string $description;

    #[ORM\Column(type: "string", length: 100)]
    private string $type;

    public function getId_report()
    {
        return $this->id_report;
    }

    public function setId_report($value)
    {
        $this->id_report = $value;
    }

    public function getId_user_sender()
    {
        return $this->id_user_sender;
    }

    public function setId_user_sender($value)
    {
        $this->id_user_sender = $value;
    }

    public function getId_user_target()
    {
        return $this->id_user_target;
    }

    public function setId_user_target($value)
    {
        $this->id_user_target = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }
}
