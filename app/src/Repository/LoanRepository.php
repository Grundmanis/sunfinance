<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Loan;
use Error;

final class LoanRepository extends EntityRepository
{
    public function existsByReference(string $reference): bool
    {
        return (bool) $this->createQueryBuilder('l')
            ->select('1')
            ->andWhere('l.reference = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getByReference(string $reference): Loan
    {
        $loan = $this->findOneBy(['reference' => $reference]);

        if (!$loan) {
            throw new Error($reference);
        }

        return $loan;
    }
}
