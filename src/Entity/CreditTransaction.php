<?php

namespace App\Entity;

use App\Enum\CreditTransactionReason;
use App\Enum\CreditTransactionType;
use App\Repository\CreditTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditTransactionRepository::class)]
class CreditTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'creditTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $idUser = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Reservation $idReservation = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(enumType: CreditTransactionType::class)]
    private ?CreditTransactionType $transactionType = null;

    #[ORM\Column(enumType: CreditTransactionReason::class)]
    private ?CreditTransactionReason $transactionReason = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getIdReservation(): ?Reservation
    {
        return $this->idReservation;
    }

    public function setIdReservation(?Reservation $idReservation): static
    {
        $this->idReservation = $idReservation;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTransactionType(): ?CreditTransactionType
    {
        return $this->transactionType;
    }

    public function setTransactionType(CreditTransactionType $transactionType): static
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    public function getTransactionReason(): ?CreditTransactionReason
    {
        return $this->transactionReason;
    }

    public function setTransactionReason(CreditTransactionReason $transactionReason): static
    {
        $this->transactionReason = $transactionReason;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
