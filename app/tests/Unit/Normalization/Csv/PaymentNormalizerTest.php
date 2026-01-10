<?php

namespace Tests\Unit\Normalization\Csv;

use App\Normalization\Csv\PaymentNormalizer;
use PHPUnit\Framework\TestCase;

class PaymentNormalizerTest extends TestCase
{
    private PaymentNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new PaymentNormalizer();
    }

    public function testNormalizeValidRecord(): void
    {
        $record = [
            'paymentDate' => '20221212071629',
            'payerName' => 'john',
            'payerSurname' => 'DOE',
            'amount' => '100.50',
            'nationalSecurityNumber' => '123456789',
            'description' => 'Loan number LN12345678',
            'paymentReference' => ' ref123 ',
        ];

        $normalized = $this->normalizer->normalize($record);

        $this->assertEquals('2022-12-12 07:16:29', $normalized->paymentDate);
        $this->assertEquals('John', $normalized->firstName);
        $this->assertEquals('Doe', $normalized->lastName);
        $this->assertEquals(100.50, $normalized->amount);
        $this->assertEquals('123456789', $normalized->nationalSecurityNumber);
        $this->assertEquals('Loan number LN12345678', $normalized->description);
        $this->assertEquals('REF123', $normalized->refId);
        $this->assertEquals('LN12345678', $normalized->loanNumber);
    }

    public function testNormalizeInvalidDate(): void
    {
        $record = [
            'paymentDate' => 'invalid-date',
            'payerName' => 'john',
            'payerSurname' => 'DOE',
            'amount' => '100.50',
            'nationalSecurityNumber' => '123456789',
            'description' => 'Loan number LNA2345678',
            'paymentReference' => ' ref123 ',
        ];

        $normalized = $this->normalizer->normalize($record);

        $this->assertNull($normalized->paymentDate);
    }
}
