<?php

namespace App\Listener;

use App\Contracts\Communication\EmailSenderInterface;
use App\Contracts\Loggers\LoggerInterface;
use App\Event\LoanPaidEvent;
use App\Repository\CustomerRepository;

final class LoanPaidEmailListener
{
    public function __construct(
        private readonly EmailSenderInterface $emailSender,
        private readonly CustomerRepository $customerRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(LoanPaidEvent $event): void
    {
        // FIXME: implement relation in Loan entity to get customer directly 
        $customer = $this->customerRepository->find($event->loan->getCustomerId());
        if (!$customer) {
            $this->logger->error('Customer not found for loan ID: ' . $event->loan->getId());
            return;
        }

        if (!$customer->getEmail()) {
            $this->logger->info('Customer doesnt have an email, skipping');
            return;
        }

        // FIXME: create a LoanPaidEmail class to handle email content
        $this->emailSender->send(
            to: $customer->getEmail(),
            subject: 'Your loan has been paid off',
            body: "Thx 4 the payment"
        );
    }
}
