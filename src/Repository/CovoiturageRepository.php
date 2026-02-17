<?php

namespace App\Repository;

use App\Entity\Covoiturage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\CovoiturageSearch;

class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }

    public function search(CovoiturageSearch $search, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.idVehicule', 'v')
            ->addSelect('v');

        $departure = trim((string) $search->getPlaceDeparture());
        $arrival   = trim((string) $search->getPlaceArrival());

        if ($departure !== '') {
            $qb->andWhere('LOWER(c.placeDeparture) LIKE :departure')
               ->setParameter('departure', '%'.mb_strtolower($departure).'%');
        }

        if ($arrival !== '') {
            $qb->andWhere('LOWER(c.placeArrival) LIKE :arrival')
               ->setParameter('arrival', '%'.mb_strtolower($arrival).'%');
        }

        if ($search->getDateDeparture()) {
            $date = \DateTimeImmutable::createFromInterface($search->getDateDeparture());
            $start = $date->setTime(0, 0, 0);
            $end   = $start->modify('+1 day');

            $qb->andWhere('c.dateDeparture >= :start')
               ->andWhere('c.dateDeparture < :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }

        if (!empty($filters['energy'])) {
            $qb->andWhere('v.energy = :energy')
               ->setParameter('energy', $filters['energy']);
        }

        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('c.price <= :maxPrice')
               ->setParameter('maxPrice', (int) $filters['maxPrice']);
        }

        if (!empty($filters['maxDuration'])) {
            $qb->andWhere('c.travelTime <= :maxDuration')
               ->setParameter('maxDuration', (int) $filters['maxDuration']);
        }

        if (!empty($filters['minPlaces'])) {
            $qb->andWhere('c.placesNbr >= :minPlaces')
               ->setParameter('minPlaces', (int) $filters['minPlaces']);
        }

        return $qb->orderBy('c.dateDeparture', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
