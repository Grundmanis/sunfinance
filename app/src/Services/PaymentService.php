<?php

namespace App\Services;

use App\Contracts\Loggers\LoggerInterface;
use App\Entity\Payment;
use App\Event\LoanPaidEvent;
use App\Event\PaymentReceivedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentService
{

    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    // TODO: change array to DTO
    public function processPayment(array $record)
    {
        // TODO: move to validation logic
        $loan = $this->entityManager->getRepository('App\Entity\Loan')
            // TODO: add additional state filter
            ->findOneBy(['reference' => $record['loanNumber']]);
        if (!$loan) {
            $this->logger->warning('Unknown loan number', [
                'loanNumber' => $record['loanNumber'],
            ]);
            return;
            // return PaymentImportCommand::UNKNOWN_LOAN_NUMBER;
        }

        $payment = new Payment();
        $payment->setLoanId($loan->getId());
        $payment->setPaymentDate(new \DateTime($record['paymentDate']));
        $payment->setFirstName($record['firstName']);
        $payment->setLastName($record['lastName']);
        $payment->setAmount($record['amount']);
        $payment->setNationalSecurityNumber($record['nationalSecurityNumber'] ?? null);
        $payment->setDescription($record['description']);
        $payment->setRefId($record['refId']);
        $payment->setLoanRef($record['loanNumber']);

        // TODO: test precision issues with decimal calculations
        if ($record['amount'] === $loan->getAmountToPay()) {
            $this->logger->info('Payment matches loan amount to pay', [
                'loanNumber' => $record['loanNumber'],
            ]);
            // TODO: use constants for states
            $loan->setState('paid');
            $loan->setAmountToPay('0');
            $this->entityManager->persist($loan);
            $payment->setState('assigned');

            // TODO: do not send multiple sms/email to the same customer 
            $this->eventDispatcher->dispatch(
                new LoanPaidEvent($loan),
                'loan.fully_paid'
            );
        } else if ($record['amount'] < $loan->getAmountToPay()) {

            $this->logger->info('Payment amount is less than loan amount to pay', [
                'loanNumber' => $record['loanNumber'],
            ]);

            $newAmountToPay = bcsub($loan->getAmountToPay(), $record['amount'], 2);
            $loan->setAmountToPay($newAmountToPay);
            $this->entityManager->persist($loan);
            $payment->setState('assigned');

            // TODO: do not send multiple sms/email to the same customer 
            $this->eventDispatcher->dispatch(
                new PaymentReceivedEvent($payment, $loan),
                'payment.received'
            );
        } else {
            $this->logger->info('Payment amount exceeds loan amount to pay', [
                'loanNumber' => $record['loanNumber'],
            ]);
            $loan->setState('paid');
            $loan->setAmountToPay('0');

            $payment->setState('partially_assigned');
            $this->entityManager->persist($loan);

            // DONE - Create refund payment as separate entity called "Payment Order" with all necessary information
            $refundAmount = bcsub($record['amount'], $loan->getAmountToPay(), 2);
            $refundPayment = new Payment();
            $refundPayment->setLoanId($loan->getId());
            $refundPayment->setPaymentDate(new \DateTime($record['paymentDate']));
            $refundPayment->setFirstName($record['firstName']);
            $refundPayment->setLastName($record['lastName']);
            $refundPayment->setAmount($refundAmount);
            $refundPayment->setNationalSecurityNumber($record['nationalSecurityNumber'] ?? null);
            $refundPayment->setDescription('Refund for overpayment of loan ' . $record['loanNumber']);
            $refundPayment->setRefId($record['refId'] . '-REFUND');
            $refundPayment->setLoanRef($record['loanNumber']);
            // TODO: use constants for states
            $refundPayment->setState('refund');

            $this->entityManager->persist($refundPayment);

            // TODO: change event to include refund info
            // TODO: do not send multiple sms/email to the same customer 
            $this->eventDispatcher->dispatch(
                new PaymentReceivedEvent($payment, $loan, $refundAmount),
                'payment.received'
            );
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }
}
