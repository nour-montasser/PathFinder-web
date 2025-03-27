<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\App_user;

#[ORM\Entity]
class Channel
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_channel;

        #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "channels")]
    #[ORM\JoinColumn(name: 'id_user1', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private App_user $id_user1;

        #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "channels")]
    #[ORM\JoinColumn(name: 'id_user2', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private App_user $id_user2;

    #[ORM\Column(type: "bigint")]
    private string $rating;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $time_created;

    public function getId_channel()
    {
        return $this->id_channel;
    }

    public function setId_channel($value)
    {
        $this->id_channel = $value;
    }

    public function getId_user1()
    {
        return $this->id_user1;
    }

    public function setId_user1($value)
    {
        $this->id_user1 = $value;
    }

    public function getId_user2()
    {
        return $this->id_user2;
    }

    public function setId_user2($value)
    {
        $this->id_user2 = $value;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function getTime_created()
    {
        return $this->time_created;
    }

    public function setTime_created($value)
    {
        $this->time_created = $value;
    }
}
