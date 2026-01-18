<?php

namespace App\Entity;

use App\Repository\QuizSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizSessionRepository::class)]
class QuizSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quizSessions')]
    private ?User $user = null;

    #[ORM\OneToOne(inversedBy: 'quizSession', cascade: ['persist', 'remove'])]
    private ?Invitation $invitation = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    private ?float $finalScore = null;

    /**
     * @var Collection<int, UserReponses>
     */
    #[ORM\OneToMany(targetEntity: UserReponses::class, mappedBy: 'Session', orphanRemoval: true)]
    private Collection $userReponses;

    public function __construct()
    {
        $this->userReponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getInvitation(): ?Invitation
    {
        return $this->invitation;
    }

    public function setInvitation(?Invitation $invitation): static
    {
        $this->invitation = $invitation;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getFinalScore(): ?float
    {
        return $this->finalScore;
    }

    public function setFinalScore(float $finalScore): static
    {
        $this->finalScore = $finalScore;

        return $this;
    }

    /**
     * @return Collection<int, UserReponses>
     */
    public function getUserReponses(): Collection
    {
        return $this->userReponses;
    }

    public function addUserReponse(UserReponses $userReponse): static
    {
        if (!$this->userReponses->contains($userReponse)) {
            $this->userReponses->add($userReponse);
            $userReponse->setSession($this);
        }

        return $this;
    }

    public function removeUserReponse(UserReponses $userReponse): static
    {
        if ($this->userReponses->removeElement($userReponse)) {
            // set the owning side to null (unless already changed)
            if ($userReponse->getSession() === $this) {
                $userReponse->setSession(null);
            }
        }

        return $this;
    }
}
