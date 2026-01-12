<?php

namespace App\Listener;

use App\Contracts\Communication\SmsSenderInterface;
use App\Contracts\Loggers\LoggerInterface;
use App\Event\LoanPaidEvent;
use App\Repository\CustomerRepository;

final class LoanPaidSmsListener
{
    public function __construct(
        private readonly SmsSenderInterface $smsSender,
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

        if (!$customer->getPhoneNumber()) {
            $this->logger->info('Customer doesnt have a phone number, skipping');
            return;
        }
        $loanReference = $event->loan->getReference();
        // separate class for the SMS Loan paid notification should be implemented
        $this->smsSender->send(
            $customer->getPhoneNumber(),
            "Your loan [$loanReference] loan has been paid off! "
        );
    }
}
