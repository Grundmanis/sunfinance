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
            'loanNumber' => 'LN12345678',
        ];

        $result = $this->validator->validate($record);

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
            'refId' => '',
            'loanNumber' => 'LN12345678',
        ];

        $result = $this->validator->validate($record);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
        $this->assertCount(6, $result->getErrors());

        $errors = $result->getErrors();
        $this->assertEquals('paymentDate', $errors[0]['propertyPath']);
        $this->assertEquals('firstName', $errors[1]['propertyPath']);
        $this->assertEquals('lastName', $errors[2]['propertyPath']);
        $this->assertEquals('amount', $errors[3]['propertyPath']);
        $this->assertEquals('refId', $errors[4]['propertyPath']);
        $this->assertEquals('description', $errors[5]['propertyPath']);
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
            'loanNumber' => 'LN12345678',
        ];

        $record2 = [
            'paymentDate' => '2023-10-02 12:00:00',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'amount' => 200.75,
            'nationalSecurityNumber' => '987654321',
            'description' => 'Loan number LN87654321',
            'refId' => 'REF123',
            'loanNumber' => 'LN12345678',
        ];

        $this->validator->validate($record1);
        $result = $this->validator->validate($record2);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEquals('refId', $result->getErrors()[0]['propertyPath']);
        $this->assertStringContainsString('Duplicate entry found for reference.', $result->getErrors()[0]['message']);
    }

    public function testMissingLoanNumberInDescription(): void
    {
        $record = [
            'paymentDate' => '2023-10-01 12:00:00',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'amount' => 100.50,
            'nationalSecurityNumber' => '123456789',
            'description' => 'Invalid description',
            'refId' => 'REF123',
            'loanNumber' => 'LN12345678',
        ];

        $result = $this->validator->validate($record);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
        $this->assertEquals('description', $result->getErrors()[0]['propertyPath']);
        $this->assertStringContainsString('must contain a loan number', $result->getErrors()[0]['message']);
    }
}
