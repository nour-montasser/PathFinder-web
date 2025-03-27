<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Message
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_message;

    #[ORM\Column(type: "string", length: 500)]
    private string $content;

    #[ORM\Column(type: "bigint")]
    private string $id_user_sender;

    #[ORM\Column(type: "string", length: 255)]
    private string $media;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $timesent;

    #[ORM\Column(type: "bigint")]
    private string $id_channel;

    public function getId_message()
    {
        return $this->id_message;
    }

    public function setId_message($value)
    {
        $this->id_message = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getId_user_sender()
    {
        return $this->id_user_sender;
    }

    public function setId_user_sender($value)
    {
        $this->id_user_sender = $value;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia($value)
    {
        $this->media = $value;
    }

    public function getTimesent()
    {
        return $this->timesent;
    }

    public function setTimesent($value)
    {
        $this->timesent = $value;
    }

    public function getId_channel()
    {
        return $this->id_channel;
    }

    public function setId_channel($value)
    {
        $this->id_channel = $value;
    }
}
