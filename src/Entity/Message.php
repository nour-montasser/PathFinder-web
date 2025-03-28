<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\App_user;
use App\Entity\Channel;

#[ORM\Entity]
class Message
{
    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private int $id_message;

    #[ORM\Column(type: "text", length: 500)]
    private string $content;

    #[ORM\ManyToOne(targetEntity: App_user::class)]
    #[ORM\JoinColumn(name: "id_user_sender", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $sender;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $media = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $time_sent;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: "messages")]
    #[ORM\JoinColumn(name: "id_channel", referencedColumnName: "id_channel", onDelete: "CASCADE")]
    private Channel $channel;

    public function getId_message(): int
    {
        return $this->id_message;
    }

    public function setId_message(int $id_message): self
    {
        $this->id_message = $id_message;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getSender(): App_user
    {
        return $this->sender;
    }

    public function setSender(App_user $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): self
    {
        $this->media = $media;
        return $this;
    }

    public function getTime_sent(): \DateTimeInterface
    {
        return $this->time_sent;
    }

    public function setTime_sent(\DateTimeInterface $time_sent): self
    {
        $this->time_sent = $time_sent;
        return $this;
    }

    public function getChannel(): Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): self
    {
        $this->channel = $channel;
        return $this;
    }
}