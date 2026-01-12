<?php

namespace App\Event;

use App\Entity\Payment;

class FailedPaymentReportEvent
{
    public function __construct(public readonly Payment $payment) {}
}
