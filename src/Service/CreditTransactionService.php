<?php

namespace App\Service;

use App\Entity\CreditTransaction;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\CreditTransactionReason;
use App\Enum\CreditTransactionType;
use Doctrine\ORM\EntityManagerInterface;

class CreditTransactionService
{
    public function __construct(private EntityManagerInterface $em) {}

     // Débite un utilisateur + enregistre la transaction
    public function debit(
        User $user,
        Reservation $reservation,
        int $amount,
        CreditTransactionReason $reason
    ): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Montant débit invalide.');
        }

        if ($user->getCredits() < $amount) {
            throw new \RuntimeException('Crédits insuffisants.');
        }

        $user->setCredits($user->getCredits() - $amount);

        $tx = (new CreditTransaction())
            ->setIdUser($user)
            ->setIdReservation($reservation)
            ->setAmount($amount)
            ->setTransactionType(CreditTransactionType::DEBIT)
            ->setTransactionReason($reason)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($tx);
    }

     //Crédit un utilisateur + enregistre la transaction
    public function credit(
    User $user,
    ?Reservation $reservation,
    int $amount,
    CreditTransactionReason $reason
    ): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Montant crédit invalide.');
        }

        $user->setCredits($user->getCredits() + $amount);

        $tx = (new CreditTransaction())
            ->setIdUser($user)
            ->setIdReservation($reservation) 
            ->setAmount($amount)
            ->setTransactionType(CreditTransactionType::CREDIT)
            ->setTransactionReason($reason)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($tx);
    }

    
     // Paiement complet d'une réservation : prix + commission plateforme (2 crédits)
    public function payReservation(User $passenger, Reservation $reservation, int $price, int $platformFee = 2): void
    {
        $this->debit($passenger, $reservation, $price, CreditTransactionReason::PAIEMENT_RESERVATION);
        $this->debit($passenger, $reservation, $platformFee, CreditTransactionReason::COMMISSION_PLATEFORME);
    }


     // Paiement du conducteur : (prix - commission) après validation fin de trajet (US11)
    public function payDriver(User $driver, Reservation $reservation, int $driverAmount): void
    {
        if ($driverAmount <= 0) {
            return; 
        }

        $this->credit($driver, $reservation, $driverAmount, CreditTransactionReason::PAIEMENT_CONDUCTEUR);
    }

     // Remboursement passager     
    public function refund(User $passenger, Reservation $reservation, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->credit($passenger, $reservation, $amount, CreditTransactionReason::REMBOURSEMENT);
    }

    public function flush(): void
    {
        $this->em->flush();
    }
}

