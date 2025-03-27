<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Application_job
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $application_id;

    #[ORM\Column(type: "bigint")]
    private string $job_offer_id;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_application;

    #[ORM\Column(type: "string", length: 50)]
    private string $status;

    #[ORM\Column(type: "bigint")]
    private string $cv_id;

    public function getApplication_id()
    {
        return $this->application_id;
    }

    public function setApplication_id($value)
    {
        $this->application_id = $value;
    }

    public function getJob_offer_id()
    {
        return $this->job_offer_id;
    }

    public function setJob_offer_id($value)
    {
        $this->job_offer_id = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getDate_application()
    {
        return $this->date_application;
    }

    public function setDate_application($value)
    {
        $this->date_application = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getCv_id()
    {
        return $this->cv_id;
    }

    public function setCv_id($value)
    {
        $this->cv_id = $value;
    }
}
