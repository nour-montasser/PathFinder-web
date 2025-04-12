<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\App_user;

#[ORM\Entity]
class Profile
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: App_user::class, inversedBy: 'profile')]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $user;

    #[ORM\Column(type: "string", length: 255)]
    private string $address;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $birthday;

    #[ORM\Column(type: "string", length: 20)]
    private string $phone;

    #[ORM\Column(type: "string", length: 255)]
    private string $current_occupation;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: "text", length: 500)]
    private string $bio;

    public function getUser(): App_user
    {
        return $this->user;
    }

    public function setUser(App_user $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getBirthday(): \DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getCurrent_occupation(): string
    {
        return $this->current_occupation;
    }

    public function setCurrent_occupation(string $current_occupation): self
    {
        $this->current_occupation = $current_occupation;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getBio(): string
    {
        return $this->bio;
    }

    public function setBio(string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }
}