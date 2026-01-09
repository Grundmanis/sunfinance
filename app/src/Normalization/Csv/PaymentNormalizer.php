<?php

namespace App\Normalization\Csv;

use App\Contracts\Normalization\PaymentNormalizerInterface;
use DateTime;
use Exception;

class PaymentNormalizer implements PaymentNormalizerInterface
{
    public function normalize(array $record): array
    {
        return [
            'paymentDate' => isset($record['paymentDate']) ? $this->transformToDate($record['paymentDate']) : null,
            'firstName' => isset($record['payerName']) ? ucfirst(strtolower(trim($record['payerName']))) : null,
            'lastName' => isset($record['payerSurname']) ? ucfirst(strtolower(trim($record['payerSurname']))) : null,
            'amount' => isset($record['amount']) ? (float) $record['amount'] : null,
            'nationalSecurityNumber' => $record['nationalSecurityNumber'] ?? null,
            'description' => isset($record['description']) ? trim($record['description']) : null,
            'refId' => isset($record['paymentReference']) ? strtoupper(trim($record['paymentReference'])) : null,
        ];
    }

    // TODO: move
    private function transformToDate(string $date): ?string
    {
        try {
            if (preg_match('/^\d{14}$/', $date)) {
                $dateTime = DateTime::createFromFormat('YmdHis', $date);
            } elseif (strtotime($date) !== false) {
                $dateTime = new DateTime($date);
            } else {
                throw new Exception("Invalid date format");
            }

            return $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
}
