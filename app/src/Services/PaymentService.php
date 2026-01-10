<?php

namespace App\Services;

use App\Contracts\Loggers\LoggerInterface;
use App\DTO\PaymentDTO;
use App\Entity\Loan;
use App\Entity\LoanState;
use App\Entity\Payment;
use App\Entity\PaymentState;
use App\Factory\PaymentFactory;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;

class PaymentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private LoanRepository $loanRepository,
        private PaymentFactory $paymentFactory,
        private PaymentEventDispatcher $paymentEventDispatcher,
    ) {}

    public function createPayment(PaymentDTO $dto): Payment
    {
        return $this->entityManager->wrapInTransaction(function () use ($dto) {
            $loan = $this->loanRepository->getByReference($dto->loanNumber);
            $comparison = bccomp($dto->amount, $loan->getAmountToPay(), 2);

            $payment = match ($comparison) {
                0  => $this->handleExactPayment($dto, $loan),
                -1 => $this->handlePartialPayment($dto, $loan),
                1  => $this->handleOverPayment($dto, $loan),
            };

            $this->entityManager->flush();
            return $payment;
        });
    }

    private function handleOverPayment(PaymentDTO $dto, Loan $loan): Payment
    {
        $this->logger->info('Overpayment detected', [
            'loanNumber' => $loan->getReference(),
            'paymentAmount' => $dto->amount,
            'amountToPay' => $loan->getAmountToPay(),
        ]);

        $refundAmount = bcsub($dto->amount, $loan->getAmountToPay(), 2);
        $payment = $this->paymentFactory->fromDto(
            $dto,
            $loan,
            $dto->amount,
            PaymentState::ASSIGNED
        );
        $loan->setState(LoanState::PAID);

        $refundPayment = $this->paymentFactory->createRefund(
            $dto,
            $loan,
            $refundAmount,
        );

        $this->entityManager->persist($loan);
        $this->entityManager->persist($payment);
        $this->entityManager->persist($refundPayment);

        $this->paymentEventDispatcher->dispatchPaymentReceived($payment, $loan, $refundAmount);
        $this->paymentEventDispatcher->dispatchLoanPaid($loan);

        return $payment;
    }

    private function handlePartialPayment(PaymentDTO $dto, Loan $loan): Payment
    {
        $this->logger->info('Partial payment received', [
            'loanNumber' => $loan->getReference(),
            'paymentAmount' => $dto->amount,
            'amountToPay' => $loan->getAmountToPay(),
        ]);

        $payment = $this->paymentFactory->fromDto(
            $dto,
            $loan,
            $dto->amount,
            PaymentState::ASSIGNED
        );

        $newAmountToPay = bcsub($loan->getAmountToPay(), $dto->amount, 2);
        $loan->setAmountToPay($newAmountToPay);

        $this->entityManager->persist($payment);
        $this->entityManager->persist($loan);

        $this->paymentEventDispatcher->dispatchPaymentReceived($payment, $loan);

        return $payment;
    }

    private function handleExactPayment(PaymentDTO $dto, Loan $loan): Payment
    {
        $this->logger->info('Exact payment received', [
            'loanNumber' => $loan->getReference(),
            'paymentAmount' => $dto->amount,
        ]);

        $payment = $this->paymentFactory->fromDto(
            $dto,
            $loan,
            $dto->amount,
            PaymentState::ASSIGNED
        );

        $loan->setState(LoanState::PAID);

        $this->entityManager->persist($loan);
        $this->entityManager->persist($payment);

        $this->paymentEventDispatcher->dispatchLoanPaid($loan);

        return $payment;
    }
}
