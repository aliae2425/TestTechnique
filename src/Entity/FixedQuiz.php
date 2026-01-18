<?php

namespace App\Entity;

use App\Repository\FixedQuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FixedQuizRepository::class)]
class FixedQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuizTemplate $Template = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\ManyToMany(targetEntity: Question::class)]
    private Collection $Questions;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $OrderIndex = [];

    public function __construct()
    {
        $this->Questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplate(): ?QuizTemplate
    {
        return $this->Template;
    }

    public function setTemplate(?QuizTemplate $Template): static
    {
        $this->Template = $Template;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->Questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->Questions->contains($question)) {
            $this->Questions->add($question);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        $this->Questions->removeElement($question);

        return $this;
    }

    public function getOrderIndex(): array
    {
        return $this->OrderIndex;
    }

    public function setOrderIndex(array $OrderIndex): static
    {
        $this->OrderIndex = $OrderIndex;

        return $this;
    }
}
