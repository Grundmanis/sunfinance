<?php

namespace App\Factory;

use App\DTO\PaymentDTO;
use App\Entity\Loan;
use App\Entity\Payment;
use App\Entity\PaymentState;

class PaymentFactory
{
    public function fromDto(PaymentDTO $dto, Loan $loan, string $amount, PaymentState $state, ?string $description = null, ?string $refSuffix = null): Payment
    {
        $payment = new Payment();
        $payment->setLoanId($loan->getId());
        $payment->setPaymentDate(new \DateTimeImmutable($dto->paymentDate));
        $payment->setFirstName($dto->firstName);
        $payment->setLastName($dto->lastName);
        $payment->setAmount($amount);
        $payment->setNationalSecurityNumber($dto->nationalSecurityNumber);
        $payment->setDescription($description ?? $dto->description);
        $payment->setRefId($dto->refId . ($refSuffix ?? ''));
        $payment->setLoanRef($dto->loanNumber);
        $payment->setState($state);

        return $payment;
    }

    public function createRefund(PaymentDTO $dto, Loan $loan, string $refundAmount): Payment
    {
        return $this->fromDto(
            $dto,
            $loan,
            $refundAmount,
            PaymentState::REFUND,
            'Refund for overpayment of loan ' . $loan->getReference(),
            '-REFUND'
        );
    }
}
