<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class PaymentValidator
{
    private array $seenReferences = [];

    public function validate(array $record, int $recordIndex)
    {
        $validator = Validation::createValidator();
        $constraints = new Assert\Collection([
            'paymentDate' => [new Assert\NotBlank(), new Assert\DateTime()],
            'firstName' => new Assert\NotBlank(),
            'lastName' => new Assert\NotBlank(),
            'amount' => [
                new Assert\NotBlank(),
                new Assert\GreaterThan(0),
            ],
            'nationalSecurityNumber' => new Assert\Optional(),
            'description' => new Assert\NotBlank(),
            'refId' => new Assert\NotBlank(),
        ]);

        $violations = $validator->validate($record, $constraints);
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = sprintf(
                "Record #%d (refId: %s, field: %s, value: %s): %s",
                $recordIndex,
                $record['refId'] ?? 'N/A',
                $violation->getPropertyPath(),
                var_export($violation->getInvalidValue(), true),
                $violation->getMessage()
            );
        }

        // Check for duplicate references
        if (isset($this->seenReferences[$record['refId']])) {
            $errors[] = sprintf(
                "Record #%d (refId: %s): Duplicate entry found for reference.",
                $recordIndex,
                $record['refId']
            );
        } else {
            $this->seenReferences[$record['refId']] = true;
        }

        // Check for loan number in description
        if (!preg_match('/LN\d{8}/', $record['description'])) {
            $errors[] = sprintf(
                "Record #%d (refId: %s): Description (%s) must contain a loan number starting with 'LN', followed by 2 letters and 8 digits.",
                $recordIndex,
                $record['refId'] ?? 'N/A',
                $record['description']
            );
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
