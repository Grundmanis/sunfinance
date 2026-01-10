<?php

namespace App\Listener;

use App\Contracts\Communication\EmailSenderInterface;
use App\Event\FailedPaymentReportEvent;

final class FailedPaymentListener
{
    private readonly EmailSenderInterface $emailSender;

    public function __construct(
        EmailSenderInterface $emailSender,
    ) {
        $this->emailSender = $emailSender;
    }

    public function __invoke(FailedPaymentReportEvent $event): void
    {
        // TODO: separate class + pass payment details to email body
        // TODO: USE IT 
        $this->emailSender->send(
            to: "support@example.com",
            subject: 'Failed payment report',
            body: "Thx 4 the payment"
        );
    }
}
