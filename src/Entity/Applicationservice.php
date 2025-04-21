<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Serviceoffre;
use App\Entity\App_user;

#[ORM\Entity]
class Applicationservice
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_app", type: "bigint")]
    private ?int $idApp = null;

    #[ORM\ManyToOne(targetEntity: Serviceoffre::class, inversedBy: "applicationservices")]
    #[ORM\JoinColumn(name: "id_service", referencedColumnName: "id_service", nullable: true)]
    private ?Serviceoffre $service = null;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "serviceApplications")]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user", nullable: true)]
    private App_user $user;

    #[ORM\Column(name: "price_offre", type: "float")]
    private float $priceOffre;

    #[ORM\Column(type: "string", length: 25)]
private string $status = 'pending';


    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $rating = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: "portfolio", type: 'string', length: 255, nullable: true)]
    private ?string $portfolio = null;

    public function getIdApp(): ?int
    {
        return $this->idApp;
    }

    public function setIdApp(int $idApp): self
    {
        $this->idApp = $idApp;
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

    public function setUser(App_user $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getPriceOffre(): float
    {
        return $this->priceOffre;
    }

    public function setPriceOffre(float $priceOffre): self
    {
        $this->priceOffre = $priceOffre;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getRating(): ?int
{
    return $this->rating;
}

public function setRating(?int $rating): self
{
    $this->rating = $rating;
    return $this;
}


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPortfolio(): ?string
    {
        return $this->portfolio;
    }

    public function setPortfolio(?string $portfolio): self
    {
        $this->portfolio = $portfolio;
        return $this;
    }




}
