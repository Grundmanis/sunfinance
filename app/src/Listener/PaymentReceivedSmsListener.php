<?php

namespace App\Listener;

use App\Contracts\Communication\SmsSenderInterface;
use App\Event\PaymentReceivedEvent;

class PaymentReceivedSmsListener
{
    private readonly SmsSenderInterface $smsSender;

    public function __construct(
        SmsSenderInterface $smsSender,
    ) {
        $this->smsSender = $smsSender;
    }

    public function __invoke(PaymentReceivedEvent $event): void
    {
        $this->smsSender->send(
            "+371222333444",
            'Payment received. Thank you!',
        );
    }
}
