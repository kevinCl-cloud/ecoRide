<?php

namespace App\Repository;

use App\Entity\CreditTransaction;
use App\Entity\User;
use App\Enum\CreditTransactionReason;
use App\Enum\CreditTransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreditTransaction>
 */
class CreditTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditTransaction::class);
    }

     //US13 - Crédits plateforme / jour sur N jours
      //Retour: [ ['day' => '2026-02-18', 'credits' => 12], ... ]
    public function platformCreditsPerDayLastDays(int $days = 7): array
    {
        $from = (new \DateTimeImmutable())
            ->modify(sprintf('-%d days', $days - 1))
            ->setTime(0, 0, 0);

        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT DATE(created_at) AS day, COALESCE(SUM(amount), 0) AS credits
            FROM credit_transaction
            WHERE created_at >= :from
              AND transaction_reason = :reason
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ";

        return $conn->executeQuery($sql, [
            'from'   => $from->format('Y-m-d H:i:s'),
            'reason' => CreditTransactionReason::COMMISSION_PLATEFORME->value,
        ])->fetchAllAssociative();
    }

     // Total crédits gagnés par la plateforme (commission)
    public function totalPlatformCredits(): int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM credit_transaction
            WHERE transaction_reason = :reason
        ";

        $total = $conn->executeQuery($sql, [
            'reason' => CreditTransactionReason::COMMISSION_PLATEFORME->value,
        ])->fetchOne();

        return (int) $total;
    }

     // Historique - dernières transactions d'un utilisateur 
    public function findLastByUser(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.idUser = :user')
            ->setParameter('user', $user)
            ->orderBy('t.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function sumByUserAndType(User $user, CreditTransactionType $type): int
    {
        $sum = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.amount), 0) AS total')
            ->andWhere('t.idUser = :user')
            ->andWhere('t.transactionType = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $sum;
    }

    public function sumByUserAndReason(User $user, CreditTransactionReason $reason): int
    {
        $sum = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.amount), 0) AS total')
            ->andWhere('t.idUser = :user')
            ->andWhere('t.transactionReason = :reason')
            ->setParameter('user', $user)
            ->setParameter('reason', $reason)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $sum;
    }

    public function findLast(int $limit = 20): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}


