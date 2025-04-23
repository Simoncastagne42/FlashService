<?php

namespace App\Repository;

use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSlot>
 */
class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    public function findAvailableSlotsForService(int $serviceId, array $reservedSlotIds): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.service = :serviceId')
            ->setParameter('serviceId', $serviceId);
    
        if (!empty($reservedSlotIds)) {
            $qb->andWhere('t.id NOT IN (:reservedIds)')
               ->setParameter('reservedIds', $reservedSlotIds);
        }
    
        return $qb->getQuery()->getResult();
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
