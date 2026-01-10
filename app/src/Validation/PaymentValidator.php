<?php

namespace App\Validation;

use App\DTO\PaymentDTO;
use App\Repository\LoanRepository;
use App\Repository\PaymentRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaymentValidator
{
    public function __construct(
        private LoanRepository $loanRepository,
        private PaymentRepository $paymentRepository,
        private ValidatorInterface $validator,
    ) {}

    public function validate(PaymentDTO $dto): ValidationResult
    {
        $violations = $this->validator->validate($dto);
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'propertyPath' => (string) $violation->getPropertyPath(),
                'invalidValue' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
                'type' => ValidationErrorType::VALIDATION,
            ];
        }

        // does loan exists in db by refId
        $hasLoanNumberError = array_filter($errors, fn($e) => $e['propertyPath'] === 'loanNumber');
        if (!$hasLoanNumberError && $dto->loanNumber !== null) {
            $loanExists = $this->loanRepository->existsByReference($dto->loanNumber);
            if (!$loanExists) {
                $errors[] = [
                    'propertyPath' => 'loanNumber',
                    'invalidValue' => $dto->loanNumber,
                    'message' => 'Loan not found for provided loan number.',
                    'type' => ValidationErrorType::NOT_FOUND,
                ];
            }
        }

        // does payment already exists in db by refId
        $hasRefIdError = array_filter($errors, fn($e) => $e['propertyPath'] === 'refId');
        if (!$hasRefIdError && $dto->refId !== null) {
            $paymentExists = $this->paymentRepository->existsByReference($dto->refId);
            if ($paymentExists) {
                $errors[] = [
                    'propertyPath' => 'refId',
                    'invalidValue' => $dto->refId,
                    'message' => 'Payment with provided refId already exists.',
                    'type' => ValidationErrorType::DUPLICATE,
                ];
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
