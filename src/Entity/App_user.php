<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Channel;

#[ORM\Entity]
class App_user
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "bigint")]
    private string $role;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }
    #[ORM\OneToMany(mappedBy: "id_user1", targetEntity: Channel::class)]
    private Collection $channelsInitiated;
    
    public function getChannelsInitiated(): Collection
    {
        return $this->channelsInitiated;
    }
    
    public function addChannelInitiated(Channel $channel): self
    {
        if (!$this->channelsInitiated->contains($channel)) {
            $this->channelsInitiated[] = $channel;
            $channel->setId_user1($this);
        }
    
        return $this;
    }
    
    public function removeChannelInitiated(Channel $channel): self
    {
        if ($this->channelsInitiated->removeElement($channel)) {
            if ($channel->getId_user1() === $this) {
                $channel->setId_user1(null);
            }
        }
    
        return $this;
    }
    
    #[ORM\OneToMany(mappedBy: "id_user2", targetEntity: Channel::class)]
    private Collection $channelsReceived;
    
    public function getChannelsReceived(): Collection
    {
        return $this->channelsReceived;
    }
    
    public function addChannelReceived(Channel $channel): self
    {
        if (!$this->channelsReceived->contains($channel)) {
            $this->channelsReceived[] = $channel;
            $channel->setId_user2($this);
        }
    
        return $this;
    }
    
    public function removeChannelReceived(Channel $channel): self
    {
        if ($this->channelsReceived->removeElement($channel)) {
            if ($channel->getId_user2() === $this) {
                $channel->setId_user2(null);
            }
        }
    
        return $this;
    }
}    
