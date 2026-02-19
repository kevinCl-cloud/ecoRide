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
            ->distinct()
            ->leftJoin('c.idVehicule', 'v')
            ->addSelect('v');

        //  Uniquement les trajets "PREVU"
        $qb->andWhere('c.statut = :statusPrevu')
        ->setParameter('statusPrevu', \App\Enum\CovoiturageStatus::PREVU);

        //  Au moins 1 place disponible
        $qb->andWhere('c.placesNbr >= :minAvailable')
        ->setParameter('minAvailable', 1);

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
            $minPlaces = max(1, (int) $filters['minPlaces']);

            $qb->andWhere('c.placesNbr >= :minPlaces')
            ->setParameter('minPlaces', $minPlaces);
        }

        return $qb->orderBy('c.dateDeparture', 'ASC')
                ->getQuery()
                ->getResult();
    }


    public function countPerDayLastDays(int $days = 7): array
    {
        $from = (new \DateTimeImmutable())
            ->modify(sprintf('-%d days', $days - 1))
            ->setTime(0, 0, 0);

        return $this->createQueryBuilder('c')
            ->select('c.dateDeparture AS day, COUNT(c.id) AS cnt')
            ->where('c.dateDeparture >= :from')
            ->setParameter('from', $from)
            ->groupBy('c.dateDeparture')
            ->orderBy('c.dateDeparture', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }


}
