<?php

namespace Tests\Unit\Normalization\Api;

use App\Entity\Payment;
use App\Entity\PaymentState;
use App\Normalization\Api\PaymentNormalizer;
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
            'firstname' => 'john',
            'lastname' => 'DOE',
            'amount' => '100.50',
            'description' => 'Loan number LN12345678',
            'refId' => ' ref123 ',
        ];

        $normalized = $this->normalizer->normalize($record);

        $this->assertEquals('2022-12-12 07:16:29', $normalized->paymentDate);
        $this->assertEquals('John', $normalized->firstName);
        $this->assertEquals('Doe', $normalized->lastName);
        $this->assertEquals(100.50, $normalized->amount);
        $this->assertEquals('Loan number LN12345678', $normalized->description);
        $this->assertEquals('REF123', $normalized->refId);
        $this->assertEquals('LN12345678', $normalized->loanNumber);
    }

    public function testNormalizeInvalidDate(): void
    {
        $record = [
            'paymentDate' => 'invalid-date',
            'firstname' => 'john',
            'lastname' => 'DOE',
            'amount' => '100.50',
            'description' => 'Loan number LNA2345678',
            'refId' => ' ref123 ',
        ];

        $normalized = $this->normalizer->normalize($record);

        $this->assertNull($normalized->paymentDate);
    }

    public function testDenormalizePayment(): void
    {
        $payment = new Payment();
        $payment->setId(1);
        $payment->setLoanId(10);
        $payment->setLoanRef('LN12345678');
        $payment->setFirstName('John');
        $payment->setLastName('Doe');
        $payment->setState(PaymentState::ASSIGNED);
        $payment->setPaymentDate(new \DateTimeImmutable('2022-12-12 07:16:29'));
        $payment->setAmount(100.50);
        $payment->setRefId('REF123');
        $payment->setDescription('Loan number LN12345678');

        $dto = $this->normalizer->denormalize($payment);
        $this->assertEquals(1, $dto['id']);
        $this->assertEquals(10, $dto['loanId']);
        $this->assertEquals('LN12345678', $dto['loanRef']);
        $this->assertEquals('John', $dto['firstName']);
        $this->assertEquals('Doe', $dto['lastName']);
        $this->assertEquals('ASSIGNED', $dto['state']);
        $this->assertEquals('2022-12-12 07:16:29', $dto['paymentDate']);
        $this->assertEquals(100.50, $dto['amount']);
        $this->assertEquals('REF123', $dto['refId']);
        $this->assertEquals('Loan number LN12345678', $dto['description']);
    }
}
