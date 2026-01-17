<?php

namespace App\Entity;

use App\Repository\QuizRuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizRuleRepository::class)]
class QuizRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $theme = null;

    #[ORM\Column(length: 255)]
    private ?string $level = null;

    #[ORM\Column]
    private ?int $Quantity = null;

    /**
     * @var Collection<int, QuizTemplate>
     */
    #[ORM\OneToMany(targetEntity: QuizTemplate::class, mappedBy: 'Rules')]
    private Collection $Rules;

    public function __construct()
    {
        $this->Rules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): static
    {
        $this->Quantity = $Quantity;

        return $this;
    }

    /**
     * @return Collection<int, QuizTemplate>
     */
    public function getRules(): Collection
    {
        return $this->Rules;
    }

    public function addRule(QuizTemplate $rule): static
    {
        if (!$this->Rules->contains($rule)) {
            $this->Rules->add($rule);
            $rule->setRules($this);
        }

        return $this;
    }

    public function removeRule(QuizTemplate $rule): static
    {
        if ($this->Rules->removeElement($rule)) {
            // set the owning side to null (unless already changed)
            if ($rule->getRules() === $this) {
                $rule->setRules(null);
            }
        }

        return $this;
    }
}
