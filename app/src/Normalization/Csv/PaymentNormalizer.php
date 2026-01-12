<?php

namespace App\Normalization\Csv;

use App\Contracts\Normalization\PaymentNormalizerInterface;
use App\DTO\PaymentDTO;
use App\Utils\DateUtil;
use App\Utils\LoanNumberExtractor;

class PaymentNormalizer implements PaymentNormalizerInterface
{
    public function normalize(array $record): PaymentDTO
    {
        $data = [
            'paymentDate' => isset($record['paymentDate']) ? DateUtil::transform($record['paymentDate']) : null,
            'firstName' => isset($record['payerName']) ? ucfirst(strtolower(trim($record['payerName']))) : null,
            'lastName' => isset($record['payerSurname']) ? ucfirst(strtolower(trim($record['payerSurname']))) : null,
            'amount' => isset($record['amount']) ? (float) $record['amount'] : null,
            'nationalSecurityNumber' => $record['nationalSecurityNumber'] ?? null,
            'description' => isset($record['description']) ? trim($record['description']) : null,
            'refId' => isset($record['paymentReference']) ? strtoupper(trim($record['paymentReference'])) : null,
            'loanNumber' => isset($record['description']) ? LoanNumberExtractor::extractLoanNumber($record['description']) : null,
        ];
        return PaymentDTO::fromArray($data);
    }
}
