<?php

namespace App\Normalization\Csv;

use App\Contracts\Normalization\PaymentNormalizerInterface;
use App\Transformers\DateTransformer;

class PaymentNormalizer implements PaymentNormalizerInterface
{
    private DateTransformer $dateTransformer;

    public function __construct(DateTransformer $dateTransformer)
    {
        $this->dateTransformer = $dateTransformer;
    }

    public function normalize(array $record): array
    {
        return [
            'paymentDate' => isset($record['paymentDate']) ? $this->dateTransformer->transform($record['paymentDate']) : null,
            'firstName' => isset($record['payerName']) ? ucfirst(strtolower(trim($record['payerName']))) : null,
            'lastName' => isset($record['payerSurname']) ? ucfirst(strtolower(trim($record['payerSurname']))) : null,
            'amount' => isset($record['amount']) ? (float) $record['amount'] : null,
            'nationalSecurityNumber' => $record['nationalSecurityNumber'] ?? null,
            'description' => isset($record['description']) ? trim($record['description']) : null,
            'refId' => isset($record['paymentReference']) ? strtoupper(trim($record['paymentReference'])) : null,
        ];
    }
}
