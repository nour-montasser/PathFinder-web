<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Serviceoffre;

#[ORM\Entity]
class Applicationservice
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_app;

        #[ORM\ManyToOne(targetEntity: Serviceoffre::class, inversedBy: "applicationservices")]
    #[ORM\JoinColumn(name: 'id_service', referencedColumnName: 'id_service', onDelete: 'CASCADE')]
    private Serviceoffre $id_service;

    #[ORM\Column(type: "float")]
    private float $price_offre;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "string", length: 25)]
    private string $status;

    #[ORM\Column(type: "boolean")]
    private bool $rating;

    public function getId_app()
    {
        return $this->id_app;
    }

    public function setId_app($value)
    {
        $this->id_app = $value;
    }

    public function getId_service()
    {
        return $this->id_service;
    }

    public function setId_service($value)
    {
        $this->id_service = $value;
    }

    public function getPrice_offre()
    {
        return $this->price_offre;
    }

    public function setPrice_offre($value)
    {
        $this->price_offre = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }
}
