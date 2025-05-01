<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Serviceoffre;
use App\Entity\App_user;

#[ORM\Entity]
class Applicationservice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $id_app;

    #[ORM\ManyToOne(targetEntity: Serviceoffre::class, inversedBy: "applicationservices")]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id_service', onDelete: 'CASCADE')]
    private Serviceoffre $service;

    #[ORM\ManyToOne(targetEntity: App_user::class,inversedBy: "serviceApplications")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user')]
    private App_user $user;

    #[ORM\Column(type: "float")]
    private float $price_offre;

    #[ORM\Column(type: "string", length: 25)]
    private string $status;

    #[ORM\Column(type: "boolean")]
    private bool $rating;

    public function getId_app(): int
    {
        return $this->id_app;
    }

    public function setId_app(int $value): self
    {
        $this->id_app = $value;
        return $this;
    }

    public function getService(): Serviceoffre
    {
        return $this->service;
    }

    public function setService(?Serviceoffre $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getUser(): App_user
    {
        return $this->user;
    }

    public function setUser(?App_user $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getPrice_offre(): float
    {
        return $this->price_offre;
    }

    public function setPrice_offre(float $value): self
    {
        $this->price_offre = $value;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $value): self
    {
        $this->status = $value;
        return $this;
    }

    public function getRating(): bool
    {
        return $this->rating;
    }

    public function setRating(bool $value): self
    {
        $this->rating = $value;
        return $this;
    }
}   