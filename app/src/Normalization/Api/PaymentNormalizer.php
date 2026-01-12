<?php

namespace App\Normalization\Api;

use App\Contracts\Normalization\PaymentNormalizerInterface;
use App\DTO\PaymentDTO;
use App\Entity\Payment;
use App\Utils\DateUtil;
use App\Utils\LoanNumberExtractor;

final class PaymentNormalizer implements PaymentNormalizerInterface
{
    public function normalize(array $record): PaymentDTO
    {
        $data = [
            'paymentDate' => isset($record['paymentDate']) ? DateUtil::transform($record['paymentDate']) : null,
            'firstName' => isset($record['firstname']) ? ucfirst(strtolower(trim($record['firstname']))) : null,
            'lastName' => isset($record['lastname']) ? ucfirst(strtolower(trim($record['lastname']))) : null,
            'amount' => isset($record['amount']) ? (float) $record['amount'] : null,
            'description' => isset($record['description']) ? trim($record['description']) : null,
            'refId' => isset($record['refId']) ? strtoupper(trim($record['refId'])) : null,
            'loanNumber' => isset($record['description']) ? LoanNumberExtractor::extractLoanNumber($record['description']) : null,
        ];

        return PaymentDTO::fromArray($data);
    }

    public function denormalize(Payment $payment): array
    {
        return [
            'id' => $payment->getId(),
            'loanId' => $payment->getLoanId(),
            'loanRef' => $payment->getLoanRef(),
            'firstName' => $payment->getFirstName(),
            'lastName' => $payment->getLastName(),
            'state' => $payment->getState()->value,
            'paymentDate' => $payment->getPaymentDate()->format('Y-m-d H:i:s'),
            'amount' => (float) $payment->getAmount(),
            'refId' => $payment->getRefId(),
            'description' => $payment->getDescription(),
        ];
    }
}
