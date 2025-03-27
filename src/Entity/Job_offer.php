<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Job_offer
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_offer;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 1000)]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $datePosted;

    #[ORM\Column(type: "string", length: 255)]
    private string $type;

    #[ORM\Column(type: "integer")]
    private int $number_of_spots;

    #[ORM\Column(type: "string", length: 255)]
    private string $required_education;

    #[ORM\Column(type: "string", length: 255)]
    private string $required_experience;

    #[ORM\Column(type: "string", length: 255)]
    private string $skills;

    #[ORM\Column(type: "string", length: 255)]
    private string $field;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    public function getId_offer()
    {
        return $this->id_offer;
    }

    public function setId_offer($value)
    {
        $this->id_offer = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
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

    public function getDatePosted()
    {
        return $this->datePosted;
    }

    public function setDatePosted($value)
    {
        $this->datePosted = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getNumber_of_spots()
    {
        return $this->number_of_spots;
    }

    public function setNumber_of_spots($value)
    {
        $this->number_of_spots = $value;
    }

    public function getRequired_education()
    {
        return $this->required_education;
    }

    public function setRequired_education($value)
    {
        $this->required_education = $value;
    }

    public function getRequired_experience()
    {
        return $this->required_experience;
    }

    public function setRequired_experience($value)
    {
        $this->required_experience = $value;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function setSkills($value)
    {
        $this->skills = $value;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField($value)
    {
        $this->field = $value;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($value)
    {
        $this->address = $value;
    }
}
