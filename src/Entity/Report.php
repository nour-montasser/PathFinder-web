<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private string $id_report;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "sentReports")]
    #[ORM\JoinColumn(name: "id_user_sender", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $userSender;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "receivedReports")]
    #[ORM\JoinColumn(name: "id_user_target", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $userTarget;

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

    public function getUserSender(): App_user
    {
        return $this->userSender;
    }

    public function setUserSender(App_user $userSender): self
    {
        $this->userSender = $userSender;
        return $this;
    }

    public function getUserTarget(): App_user
    {
        return $this->userTarget;
    }

    public function setUserTarget(App_user $userTarget): self
    {
        $this->userTarget = $userTarget;
        return $this;
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