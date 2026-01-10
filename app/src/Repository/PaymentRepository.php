<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository
{
    public function existsByReference(string $reference): bool
    {
        return (bool) $this->createQueryBuilder('p')
            ->select('1')
            ->andWhere('p.refId = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
