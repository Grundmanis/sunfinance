<?php

namespace App\Services;

use App\Contracts\Loggers\LoggerInterface;
use App\DTO\PaymentDTO;
use App\Entity\Payment;
use App\Event\LoanPaidEvent;
use App\Event\PaymentReceivedEvent;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentService
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;
    private LoanRepository $loanRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        LoanRepository $loanRepository
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->loanRepository = $loanRepository;
    }

    public function processPayment(PaymentDTO $paymentDTO)
    {
        $loan = $this->loanRepository->getByReference($paymentDTO->loanNumber);

        $payment = new Payment();
        $payment->setLoanId($loan->getId());
        $payment->setPaymentDate(new \DateTime($paymentDTO->paymentDate));
        $payment->setFirstName($paymentDTO->firstName);
        $payment->setLastName($paymentDTO->lastName);
        $payment->setAmount($paymentDTO->amount);
        $payment->setNationalSecurityNumber($paymentDTO->nationalSecurityNumber ?? null);
        $payment->setDescription($paymentDTO->description);
        $payment->setRefId($paymentDTO->refId);
        $payment->setLoanRef($paymentDTO->loanNumber);

        // TODO: test precision issues with decimal calculations
        if ($paymentDTO->amount === $loan->getAmountToPay()) {
            $this->logger->info('Payment matches loan amount to pay', [
                'loanNumber' => $paymentDTO->loanNumber,
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
        } else if ($paymentDTO->amount < $loan->getAmountToPay()) {

            $this->logger->info('Payment amount is less than loan amount to pay', [
                'loanNumber' => $paymentDTO->loanNumber,
            ]);

            $newAmountToPay = bcsub($loan->getAmountToPay(), $paymentDTO->amount, 2);
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
                'loanNumber' => $paymentDTO->loanNumber,
            ]);
            $loan->setState('paid');
            $loan->setAmountToPay('0');

            $payment->setState('partially_assigned');
            $this->entityManager->persist($loan);

            // DONE - Create refund payment as separate entity called "Payment Order" with all necessary information
            $refundAmount = bcsub($paymentDTO->amount, $loan->getAmountToPay(), 2);
            $refundPayment = new Payment();
            $refundPayment->setLoanId($loan->getId());
            $refundPayment->setPaymentDate(new \DateTime($paymentDTO->paymentDate));
            $refundPayment->setFirstName($paymentDTO->firstName);
            $refundPayment->setLastName($paymentDTO->lastName);
            $refundPayment->setAmount($refundAmount);
            $refundPayment->setNationalSecurityNumber($paymentDTO->nationalSecurityNumber ?? null);
            $refundPayment->setDescription('Refund for overpayment of loan ' . $paymentDTO->loanNumber);
            $refundPayment->setRefId($paymentDTO->refId . '-REFUND');
            $refundPayment->setLoanRef($paymentDTO->loanNumber);
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
