<?php

namespace App\Listener;

use App\Contracts\Communication\EmailSenderInterface;
use App\Event\LoanPaidEvent;

final class LoanPaidEmailListener
{
    private readonly EmailSenderInterface $emailSender;

    public function __construct(
        EmailSenderInterface $emailSender,
    ) {
        $this->emailSender = $emailSender;
    }

    public function __invoke(LoanPaidEvent $event): void
    {
        // TODO: email Loan paid class should be implemented
        // TODO: Get phone number from loan or user entity
        $this->emailSender->send(
            to: "test@test.lv",
            subject: 'Your loan has been paid off',
            body: "Thx 4 the payment"
        );
    }
}
