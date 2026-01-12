<?php

namespace App\Services;

use App\Entity\Loan;
use App\Entity\Payment;
use App\Event\FailedPaymentReportEvent;
use App\Event\LoanPaidEvent;
use App\Event\PaymentReceivedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentEventDispatcher
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function dispatchPaymentReceived(Payment $payment, Loan $loan, ?Payment $refundPayment = null): void
    {
        $this->dispatcher->dispatch(new PaymentReceivedEvent($payment, $loan, $refundPayment), 'payment.received'); // TODO: enums for the events
    }

    public function dispatchLoanPaid(Loan $loan): void
    {
        $this->dispatcher->dispatch(new LoanPaidEvent($loan), 'loan.fully_paid');
    }

    public function dispatchFailedPaymentReport(Payment $payment): void
    {
        $this->dispatcher->dispatch(new FailedPaymentReportEvent($payment), 'payment.failed_report');
    }
}
