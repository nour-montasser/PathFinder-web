<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\App_user;

#[ORM\Entity]
class Channel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $id_channel;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "channelsInitiated")]
    #[ORM\JoinColumn(name: "id_user1", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $initiator;

    #[ORM\ManyToOne(targetEntity: App_user::class, inversedBy: "channelsReceived")]
    #[ORM\JoinColumn(name: "id_user2", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private App_user $receiver;

    #[ORM\Column(type: "integer")]
    private int $rating;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $time_created;

    public function getId_channel(): int
    {
        return $this->id_channel;
    }

    public function setId_channel(int $id_channel): self
    {
        $this->id_channel = $id_channel;
        return $this;
    }

    public function getInitiator(): App_user
    {
        return $this->initiator;
    }

    public function setInitiator(App_user $initiator): self
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function getReceiver(): App_user
    {
        return $this->receiver;
    }

    public function setReceiver(App_user $receiver): self
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getTime_created(): \DateTimeInterface
    {
        return $this->time_created;
    }

    public function setTime_created(\DateTimeInterface $time_created): self
    {
        $this->time_created = $time_created;
        return $this;
    }

    public function getId(): int
{
    return $this->id_channel;
}

}