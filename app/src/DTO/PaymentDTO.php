<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PaymentDTO extends DTO
{
    #[Assert\NotBlank]
    #[Assert\DateTime]
    public ?string $paymentDate = null;

    #[Assert\NotBlank]
    public ?string $firstName = null;

    #[Assert\NotBlank]
    public ?string $lastName = null;

    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    public ?float $amount = null;

    public ?string $nationalSecurityNumber = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    #[Assert\NotBlank]
    public ?string $refId = null;

    #[Assert\NotBlank]
    #[Assert\Regex('/^LN\d{8}$/')]
    public ?string $loanNumber = null;

    /** Cross-field rule: description must contain LN######## */
    #[Assert\Callback]
    public function validateDescriptionContainsLoan(ExecutionContextInterface $context): void
    {
        if ($this->description !== null && !preg_match('/LN\d{8}/', $this->description)) {
            $context->buildViolation("Description must contain a loan number like 'LN' followed by 8 digits (e.g., LN12345678).")
                ->atPath('description')
                ->addViolation();
        }
    }
}
