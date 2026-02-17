<?php

namespace App\Repository;

use App\Entity\Covoiturage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\CovoiturageSearch;

/**
 * @extends ServiceEntityRepository<Covoiturage>
 */
class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }


    public function search(CovoiturageSearch $search): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($search->placeDeparture) {
            $qb->andWhere('c.placeDeparture LIKE :departure')
            ->setParameter('departure', '%'.$search->placeDeparture.'%');
        }

        if ($search->placeArrival) {
            $qb->andWhere('c.placeArrival LIKE :arrival')
            ->setParameter('arrival', '%'.$search->placeArrival.'%');
        }

        if ($search->dateDeparture) {
            $qb->andWhere('c.dateDeparture = :date')
            ->setParameter('date', $search->dateDeparture);
        }

        return $qb->getQuery()->getResult();
    }

}
