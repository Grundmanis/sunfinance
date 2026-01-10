<?php

namespace App\Services;

use App\Entity\Loan;
use App\Entity\Payment;
use App\Event\LoanPaidEvent;
use App\Event\PaymentReceivedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class PaymentEventDispatcher
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function dispatchPaymentReceived(Payment $payment, Loan $loan, ?string $refundAmount = null): void
    {
        $this->dispatcher->dispatch(new PaymentReceivedEvent($payment, $loan, $refundAmount), 'payment.received');
    }

    public function dispatchLoanPaid(Loan $loan): void
    {
        $this->dispatcher->dispatch(new LoanPaidEvent($loan), 'loan.fully_paid');
    }
}
