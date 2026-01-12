<?php

namespace App\Listener;

use App\Contracts\Communication\EmailSenderInterface;
use App\Contracts\Loggers\LoggerInterface;
use App\Event\PaymentReceivedEvent;
use App\Repository\CustomerRepository;

final class PaymentReceivedEmailListener
{
    public function __construct(
        private readonly EmailSenderInterface $emailSender,
        private readonly CustomerRepository $customerRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(PaymentReceivedEvent $event): void
    {
        $customer = $this->customerRepository->find($event->loan->getCustomerId());
        if (!$customer) {
            $this->logger->error('Customer not found for loan ID: ' . $event->loan->getId());
            return;
        }

        if (!$customer->getEmail()) {
            $this->logger->info('Customer doesnt have an email, skipping');
            return;
        }

        // TODO: payment received class should be created 
        $this->emailSender->send(
            to: $customer->getEmail(),
            subject: 'Ur payment has been received',
            body: "Thx 4 the payment"
        );
    }
}
