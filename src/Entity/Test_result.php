<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Test_result
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_result;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "bigint")]
    private string $id_test;

    #[ORM\Column(type: "float")]
    private float $result;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "boolean")]
    private bool $status;

    public function getId_result()
    {
        return $this->id_result;
    }

    public function setId_result($value)
    {
        $this->id_result = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getId_test()
    {
        return $this->id_test;
    }

    public function setId_test($value)
    {
        $this->id_test = $value;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($value)
    {
        $this->result = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }
}
