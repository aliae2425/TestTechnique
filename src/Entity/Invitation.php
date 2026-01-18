<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\ManyToOne]
    private ?QuizTemplate $quizTemplate = null;

    #[ORM\OneToOne(mappedBy: 'invitation', cascade: ['persist', 'remove'])]
    private ?QuizSession $quizSession = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getQuizTemplate(): ?QuizTemplate
    {
        return $this->quizTemplate;
    }

    public function setQuizTemplate(?QuizTemplate $quizTemplate): static
    {
        $this->quizTemplate = $quizTemplate;

        return $this;
    }

    public function getQuizSession(): ?QuizSession
    {
        return $this->quizSession;
    }

    public function setQuizSession(?QuizSession $quizSession): static
    {
        // unset the owning side of the relation if necessary
        if ($quizSession === null && $this->quizSession !== null) {
            $this->quizSession->setInvitation(null);
        }

        // set the owning side of the relation if necessary
        if ($quizSession !== null && $quizSession->getInvitation() !== $this) {
            $quizSession->setInvitation($this);
        }

        $this->quizSession = $quizSession;

        return $this;
    }
}
