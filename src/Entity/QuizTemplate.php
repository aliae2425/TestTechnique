<?php

namespace App\Entity;

use App\Repository\QuizTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizTemplateRepository::class)]
class QuizTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Titre = null;

    #[ORM\Column(length: 255)]
    private ?string $Mode = null;

    #[ORM\Column(length: 255)]
    private ?string $Type = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeLimit = null;

    #[ORM\ManyToOne(inversedBy: 'Rules')]
    private ?QuizRule $Rules = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(string $Titre): static
    {
        $this->Titre = $Titre;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->Mode;
    }

    public function setMode(string $Mode): static
    {
        $this->Mode = $Mode;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;

        return $this;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(?int $timeLimit): static
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    public function getRules(): ?QuizRule
    {
        return $this->Rules;
    }

    public function setRules(?QuizRule $Rules): static
    {
        $this->Rules = $Rules;

        return $this;
    }
}
