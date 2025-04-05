<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\AppUser;

#[ORM\Entity]
class Channel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private int $id_channel;

    #[ORM\ManyToOne(targetEntity: AppUser::class, inversedBy: "channelsInitiated")]
    #[ORM\JoinColumn(name: "id_user1", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private AppUser $initiator;

    #[ORM\ManyToOne(targetEntity: AppUser::class, inversedBy: "channelsReceived")]
    #[ORM\JoinColumn(name: "id_user2", referencedColumnName: "id_user", onDelete: "CASCADE")]
    private AppUser $receiver;

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

    public function getInitiator(): AppUser
    {
        return $this->initiator;
    }

    public function setInitiator(AppUser $initiator): self
    {
        $this->initiator = $initiator;
        return $this;
    }

    public function getReceiver(): AppUser
    {
        return $this->receiver;
    }

    public function setReceiver(AppUser $receiver): self
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
}