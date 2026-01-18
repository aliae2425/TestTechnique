<?php

namespace App\Entity;

use App\Repository\UserReponsesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserReponsesRepository::class)]
class UserReponses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userReponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizSession $Session = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $Question = null;

    #[ORM\ManyToOne]
    private ?Answer $Reponse = null;

    #[ORM\Column]
    private ?float $timeSpent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?QuizSession
    {
        return $this->Session;
    }

    public function setSession(?QuizSession $Session): static
    {
        $this->Session = $Session;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->Question;
    }

    public function setQuestion(?Question $Question): static
    {
        $this->Question = $Question;

        return $this;
    }

    public function getReponse(): ?Answer
    {
        return $this->Reponse;
    }

    public function setReponse(?Answer $Reponse): static
    {
        $this->Reponse = $Reponse;

        return $this;
    }

    public function getTimeSpent(): ?float
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(float $timeSpent): static
    {
        $this->timeSpent = $timeSpent;

        return $this;
    }
}
