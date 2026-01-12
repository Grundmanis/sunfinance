<?php

namespace App\Listener;

use App\Contracts\Communication\EmailSenderInterface;
use App\Event\FailedPaymentReportEvent;

final class FailedPaymentListener
{
    public function __construct(
        private readonly EmailSenderInterface $emailSender,
    ) {}

    public function __invoke(FailedPaymentReportEvent $event): void
    {
        // FIXME: separate class + pass payment details to email body
        $this->emailSender->send(
            to: "support@example.com",
            subject: "Failed Payment Report, ID: " . $event->payment->getId(),
            body: "A payment has failed. Please review the payment details in the system."
        );
    }
}
