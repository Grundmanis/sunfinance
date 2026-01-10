<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

final class PaymentRepository extends EntityRepository
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
}
