<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Service;
use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSlot>
 */
class TimeSlotRepository extends ServiceEntityRepository
{
    private ReservationRepository $reservationRepository;

    public function __construct(ManagerRegistry $registry, ReservationRepository $reservationRepository)
    {
        parent::__construct($registry, TimeSlot::class);
        $this->reservationRepository = $reservationRepository;
    }
    public function findAvailableSlotsForService(int $serviceId): array
    {
        return $this->createQueryBuilder('ts')
            ->leftJoin('App\Entity\Reservation', 'r', 'WITH', 'r.timeSlot = ts AND r.statut != :cancelled')
            ->andWhere('ts.service = :service')
            ->andWhere('r.id IS NULL') // pas de réservation active sur ce créneau
            ->andWhere('ts.date > :today OR (ts.date = :today AND ts.heureDebut > :now)')
            ->setParameter('service', $serviceId)
            ->setParameter('cancelled', \App\Entity\Reservation::STATUS_CANCELLED)
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->setParameter('now', new \DateTimeImmutable('now'))
            ->orderBy('ts.date', 'ASC')
            ->addOrderBy('ts.heureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        return array_filter($timeSlots, function (TimeSlot $slot) {
            return $this->isAvailable($slot);
        });
    }

    public function isAvailable(TimeSlot $timeSlot): bool
    {
        return !$this->reservationRepository->hasActiveReservationForTimeSlot($timeSlot);
    }


    //    /**
    //     * @return TimeSlot[] Returns an array of TimeSlot objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TimeSlot
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
