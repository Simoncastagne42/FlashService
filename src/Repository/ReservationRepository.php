<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }
    public function findActiveReservedSlotIdsForService(int $serviceId, ?int $exceptReservationId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.timeSlot) AS timeSlotId')
            ->join('r.service', 's')
            ->where('s.id = :serviceId')
            ->andWhere('r.statut != :cancelled')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('cancelled', Reservation::STATUS_CANCELLED);

        if ($exceptReservationId) {
            $qb->andWhere('r.id != :currentId')->setParameter('currentId', $exceptReservationId);
        }

        $results = $qb->getQuery()->getArrayResult();

        // extraire uniquement les IDs dans un tableau simple
        return array_column($results, 'timeSlotId');
    }
    public function hasActiveReservationForTimeSlot(TimeSlot $timeSlot): bool
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.timeSlot = :timeSlot')
            ->andWhere('r.statut IN (:statuses)')
            ->setParameter('timeSlot', $timeSlot)
            ->setParameter('statuses', [
                Reservation::STATUS_PENDING,
                Reservation::STATUS_CONFIRMED
            ])
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
