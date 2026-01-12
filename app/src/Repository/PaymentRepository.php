<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository
{
    public function existsByReference(string $reference): bool
    {
        return (bool) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.refId = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function fetchBetweenDates(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ) {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.paymentDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
