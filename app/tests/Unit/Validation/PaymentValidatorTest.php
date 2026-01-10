<?php

namespace Tests\Unit\Validation;

use App\DTO\PaymentDTO;
use App\Repository\LoanRepository;
use App\Repository\PaymentRepository;
use App\Validation\PaymentValidator;
use App\Validation\ValidationErrorType;
use App\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentValidatorTest extends TestCase
{
    private $loanRepository;
    private $paymentRepository;
    private $validator;
    private PaymentValidator $paymentValidator;

    protected function setUp(): void
    {
        $this->loanRepository = $this->createStub(LoanRepository::class);
        $this->paymentRepository = $this->createStub(PaymentRepository::class);
        $this->validator = $this->createStub(ValidatorInterface::class);

        $this->paymentValidator = new PaymentValidator(
            $this->loanRepository,
            $this->paymentRepository,
            $this->validator
        );
    }

    public function testValidPaymentDto(): void
    {
        $dto = new PaymentDTO();
        $dto->loanNumber = 'LN123';
        $dto->refId = 'PAY123';

        // No violations from Symfony validator
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        // Loan exists
        $this->loanRepository
            ->method('existsByReference')
            ->with('LN123')
            ->willReturn(true);

        // Payment does not exist
        $this->paymentRepository
            ->method('existsByReference')
            ->with('PAY123')
            ->willReturn(false);

        $result = $this->paymentValidator->validate($dto);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    public function testValidationViolations(): void
    {
        $dto = new PaymentDTO();
        $dto->loanNumber = 'LN123';

        $violation = new ConstraintViolation(
            'Loan number cannot be blank.',
            '',
            [],
            '',
            'loanNumber',
            ''
        );

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList([$violation]));

        $result = $this->paymentValidator->validate($dto);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame('loanNumber', $result->getErrors()[0]['propertyPath']);
        $this->assertSame(ValidationErrorType::VALIDATION, $result->getErrors()[0]['type']);
    }

    public function testLoanNotFound(): void
    {
        $dto = new PaymentDTO();
        $dto->loanNumber = 'LN999';

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->loanRepository
            ->method('existsByReference')
            ->with('LN999')
            ->willReturn(false);

        $result = $this->paymentValidator->validate($dto);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame('loanNumber', $result->getErrors()[0]['propertyPath']);
        $this->assertSame(ValidationErrorType::NOT_FOUND, $result->getErrors()[0]['type']);
    }

    public function testDuplicatePayment(): void
    {
        $dto = new PaymentDTO();
        $dto->refId = 'PAY123';

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->paymentRepository
            ->method('existsByReference')
            ->with('PAY123')
            ->willReturn(true);

        $result = $this->paymentValidator->validate($dto);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
        $this->assertSame('refId', $result->getErrors()[0]['propertyPath']);
        $this->assertSame(ValidationErrorType::DUPLICATE, $result->getErrors()[0]['type']);
    }
}
