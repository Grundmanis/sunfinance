<?php

namespace App\Validation;

use App\DTO\PaymentDTO;
use App\Repository\LoanRepository;
use App\Repository\PaymentRepository;
use Symfony\Component\Validator\Validation;

class PaymentValidator
{
    private LoanRepository $loanRepository;
    private PaymentRepository $paymentRepository;

    // TODO: inject validator
    // private ValidatorInterface $validator,
    public function __construct(
        LoanRepository $loanRepository,
        PaymentRepository $paymentRepository
    ) {
        $this->loanRepository = $loanRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function validate(PaymentDTO $dto): ValidationResult
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
        $violations = $validator->validate($dto);
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'propertyPath' => (string) $violation->getPropertyPath(),
                'invalidValue' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];
        }

        // loan exists in db
        $hasLoanNumberError = array_filter($errors, fn($e) => $e['propertyPath'] === 'loanNumber');
        if (!$hasLoanNumberError && $dto->loanNumber !== null) {
            $loanExists = $this->loanRepository->existsByReference($dto->loanNumber);
            if (!$loanExists) {
                $errors[] = [
                    'propertyPath' => 'loanNumber',
                    'invalidValue' => $dto->loanNumber,
                    'message' => 'Loan not found for provided loan number.',
                ];
            }
        }

        // payment is not duplicated in db
        $hasRefIdError = array_filter($errors, fn($e) => $e['propertyPath'] === 'refId');
        if (!$hasRefIdError && $dto->refId !== null) {
            $paymentExists = $this->paymentRepository->existsByReference($dto->refId);
            if ($paymentExists) {
                $errors[] = [
                    'propertyPath' => 'refId',
                    'invalidValue' => $dto->refId,
                    'message' => 'Payment with provided refId already exists.',
                ];
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }
}
