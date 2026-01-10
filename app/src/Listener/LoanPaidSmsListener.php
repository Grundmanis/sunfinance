<?php

namespace App\Listener;

use App\Contracts\Communication\SmsSenderInterface;
use App\Event\LoanPaidEvent;

class LoanPaidSmsListener
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
        $this->smsSender->send(
            "+371222333444",
            'Loan has been paid off. Thank you!',
        );
    }
}
