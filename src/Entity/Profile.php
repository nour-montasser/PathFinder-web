<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Profile
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $birthday;

    #[ORM\Column(type: "string", length: 20)]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    private string $current_occupation;

    #[ORM\Column(type: "string", length: 255)]
    private string $photo;

    #[ORM\Column(type: "string", length: 500)]
    private string $bio;

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($value)
    {
        $this->address = $value;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function setBirthday($value)
    {
        $this->birthday = $value;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($value)
    {
        $this->phone = $value;
    }

    public function getCurrent_occupation()
    {
        return $this->current_occupation;
    }

    public function setCurrent_occupation($value)
    {
        $this->current_occupation = $value;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($value)
    {
        $this->photo = $value;
    }

    public function getBio()
    {
        return $this->bio;
    }

    public function setBio($value)
    {
        $this->bio = $value;
    }
}
