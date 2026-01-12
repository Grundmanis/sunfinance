<?php

namespace App\Event;

use App\Entity\Loan;

class LoanPaidEvent
{
    public function __construct(public readonly Loan $loan) {}
}
