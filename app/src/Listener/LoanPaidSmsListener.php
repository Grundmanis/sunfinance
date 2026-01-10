<?php

namespace App\Listener;

use App\Contracts\Communication\SmsSenderInterface;
use App\Event\LoanPaidEvent;

final class LoanPaidSmsListener
{
    private readonly SmsSenderInterface $smsSender;

    public function __construct(
        SmsSenderInterface $smsSender,
    ) {
        $this->smsSender = $smsSender;
    }

    public function __invoke(LoanPaidEvent $event): void
    {
        // separate class for the SMS Loan paid notification should be implemented
        // TODO: Get phone number from loan or user entity
        $this->smsSender->send(
            "+371222333444",
            'Loan has been paid off. Thank you!',
        );
    }
}
