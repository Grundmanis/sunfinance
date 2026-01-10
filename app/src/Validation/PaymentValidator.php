<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class PaymentValidator
{
    public function validate(array $record)
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
            'loanNumber' => [new Assert\NotBlank(), new Assert\Regex('/LN\d{8}/')]
        ]);

        $violations = $validator->validate($record, $constraints);
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'propertyPath' => trim($violation->getPropertyPath(), '[]'), // remove brackets TODO: fix
                'invalidValue' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];
        }

        // Check for duplicate references
        // TODO: check duplicates in db

        // Check for loan number in description
        if (!preg_match('/LN\d{8}/', $record['description'])) {
            $errors[] = [
                'propertyPath' => 'description',
                'invalidValue' => $record['description'],
                'message' => "Description must contain a loan number starting with 'LN', followed by 2 letters and 8 digits.",
            ];
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
