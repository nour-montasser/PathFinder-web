<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Cv
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_cv;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 255)]
    private string $user_title;

    #[ORM\Column(type: "string", length: 500)]
    private string $introduction;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date_creation;

    #[ORM\Column(type: "string", length: 255)]
    private string $skills;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $last_viewed;

    #[ORM\Column(type: "boolean")]
    private bool $favorite;

    public function getId_cv()
    {
        return $this->id_cv;
    }

    public function setId_cv($value)
    {
        $this->id_cv = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getUser_title()
    {
        return $this->user_title;
    }

    public function setUser_title($value)
    {
        $this->user_title = $value;
    }

    public function getIntroduction()
    {
        return $this->introduction;
    }

    public function setIntroduction($value)
    {
        $this->introduction = $value;
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation($value)
    {
        $this->date_creation = $value;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function setSkills($value)
    {
        $this->skills = $value;
    }

    public function getLast_viewed()
    {
        return $this->last_viewed;
    }

    public function setLast_viewed($value)
    {
        $this->last_viewed = $value;
    }

    public function getFavorite()
    {
        return $this->favorite;
    }

    public function setFavorite($value)
    {
        $this->favorite = $value;
    }
}
