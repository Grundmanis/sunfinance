<?php

namespace App\Normalization\Api;

use App\Contracts\Normalization\PaymentNormalizerInterface;
use App\DTO\PaymentDTO;
use App\Transformers\DateTransformer;

class PaymentNormalizer implements PaymentNormalizerInterface
{
    private DateTransformer $dateTransformer;

    public function __construct(DateTransformer $dateTransformer)
    {
        $this->dateTransformer = $dateTransformer;
    }

    public function normalize(array $record): PaymentDTO
    {
        $data = [
            'paymentDate' => isset($record['paymentDate']) ? $this->dateTransformer->transform($record['paymentDate']) : null,
            'firstName' => isset($record['firstname']) ? ucfirst(strtolower(trim($record['firstname']))) : null,
            'lastName' => isset($record['lastname']) ? ucfirst(strtolower(trim($record['lastname']))) : null,
            'amount' => isset($record['amount']) ? (float) $record['amount'] : null,
            'description' => isset($record['description']) ? trim($record['description']) : null,
            'refId' => isset($record['refId']) ? strtoupper(trim($record['refId'])) : null,
            'loanNumber' => isset($record['description']) ? $this->extractLoanNumber($record['description']) : null,
        ];

        return PaymentDTO::fromArray($data);
    }

    // TODO: move out to a dedicated service
    private function extractLoanNumber(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        preg_match('/LN\d{8}/', $description, $matches);
        return $matches[0] ?? null;
    }
}
