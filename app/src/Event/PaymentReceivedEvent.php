<?php

namespace App\Event;

use App\Entity\Loan;
use App\Entity\Payment;

class PaymentReceivedEvent
{
    public function __construct(public readonly Payment $payment, public readonly Loan $loan, public readonly ?Payment $refundPayment = null) {}
}
