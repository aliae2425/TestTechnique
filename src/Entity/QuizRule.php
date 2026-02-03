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
    #[ORM\ManyToMany(targetEntity: QuizTemplate::class, mappedBy: 'Rules')]
    private Collection $templates;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s (%d questions)', $this->theme, $this->level, $this->Quantity);
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
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(QuizTemplate $template): static
    {
        if (!$this->templates->contains($template)) {
            $this->templates->add($template);
            $template->addRule($this);
        }

        return $this;
    }

    public function removeTemplate(QuizTemplate $template): static
    {
        if ($this->templates->removeElement($template)) {
            $template->removeRule($this);
        }

        return $this;
    }
}
