<?php

namespace Tests\Unit\Validation;

use App\Validation\PaymentValidator;
use PHPUnit\Framework\TestCase;

class PaymentValidatorTest extends TestCase
{
    private PaymentValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PaymentValidator();
    }

    public function testValidRecord(): void
    {
        $record = [
            'paymentDate' => '2023-10-01 12:00:00',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'amount' => 100.50,
            'nationalSecurityNumber' => '123456789',
            'description' => 'Loan number LN12345678',
            'refId' => 'REF123',
        ];

        $result = $this->validator->validate($record, 1);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    public function testInvalidRecord(): void
    {
        $record = [
            'paymentDate' => 'invalid-date',
            'firstName' => '',
            'lastName' => '',
            'amount' => -50,
            'nationalSecurityNumber' => null,
            'description' => 'Invalid description',
        ];

        $result = $this->validator->validate($record, 1);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());

        $this->assertStringContainsString('paymentDate', $result->getErrors()[0]);
        $this->assertStringContainsString('firstName', $result->getErrors()[1]);
        $this->assertStringContainsString('lastName', $result->getErrors()[2]);
        $this->assertStringContainsString('amount', $result->getErrors()[3]);
        $this->assertStringContainsString('refId', $result->getErrors()[4]);
        $this->assertStringContainsString('description', $result->getErrors()[5]);
    }

    public function testDuplicateRefId(): void
    {
        $record1 = [
            'paymentDate' => '2023-10-01 12:00:00',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'amount' => 100.50,
            'nationalSecurityNumber' => '123456789',
            'description' => 'Loan number LN12345678',
            'refId' => 'REF123',
        ];

        $record2 = [
            'paymentDate' => '2023-10-02 12:00:00',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'amount' => 200.75,
            'nationalSecurityNumber' => '987654321',
            'description' => 'Loan number LN87654321',
            'refId' => 'REF123',
        ];

        $this->validator->validate($record1, 1);
        $result = $this->validator->validate($record2, 2);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
        $this->assertStringContainsString('Duplicate entry', $result->getErrors()[0]);
    }
}
