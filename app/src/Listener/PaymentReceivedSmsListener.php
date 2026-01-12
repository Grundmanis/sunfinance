<?php

namespace App\Listener;

use App\Contracts\Communication\SmsSenderInterface;
use App\Contracts\Loggers\LoggerInterface;
use App\Event\PaymentReceivedEvent;
use App\Repository\CustomerRepository;

final class PaymentReceivedSmsListener
{
    public function __construct(
        private readonly SmsSenderInterface $smsSender,
        private readonly CustomerRepository $customerRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(PaymentReceivedEvent $event): void
    {
        $customer = $this->customerRepository->find($event->loan->getCustomerId());
        if (!$customer) {
            $this->logger->error('Customer not found for loan ID: ' . $event->loan->getId());
            return;
        }

        if (!$customer->getPhoneNumber()) {
            $this->logger->info('Customer doesnt have a phone number, skipping');
            return;
        }

        $this->smsSender->send(
            $customer->getPhoneNumber(),
            'Payment received. Thank you!',
        );
    }
}
