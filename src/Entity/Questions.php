<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Questions
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_question;

    #[ORM\Column(type: "string", length: 1000)]
    private string $question;

    #[ORM\Column(type: "bigint")]
    private string $id_test;

    #[ORM\Column(type: "string", length: 1000)]
    private string $responses;

    #[ORM\Column(type: "string", length: 1000)]
    private string $correct_response;

    #[ORM\Column(type: "integer")]
    private int $score;

    public function getId_question()
    {
        return $this->id_question;
    }

    public function setId_question($value)
    {
        $this->id_question = $value;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($value)
    {
        $this->question = $value;
    }

    public function getId_test()
    {
        return $this->id_test;
    }

    public function setId_test($value)
    {
        $this->id_test = $value;
    }

    public function getResponses()
    {
        return $this->responses;
    }

    public function setResponses($value)
    {
        $this->responses = $value;
    }

    public function getCorrect_response()
    {
        return $this->correct_response;
    }

    public function setCorrect_response($value)
    {
        $this->correct_response = $value;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($value)
    {
        $this->score = $value;
    }
}
