<?php

namespace App\Listener;

use App\Contracts\Communication\EmailSenderInterface;
use App\Event\PaymentReceivedEvent;

final class PaymentReceivedEmailListener
{
    private readonly EmailSenderInterface $emailSender;

    public function __construct(
        EmailSenderInterface $emailSender,
    ) {
        $this->emailSender = $emailSender;
    }

    public function __invoke(PaymentReceivedEvent $event): void
    {
        // TODO: payment received class should be created 
        // TODO: Get phone number from loan or user entity
        $this->emailSender->send(
            to: "test@test.lv",
            subject: 'Ur payment has been received',
            body: "Thx 4 the payment"
        );
    }
}
