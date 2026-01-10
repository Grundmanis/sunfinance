<?php

namespace App\Tests\Unit\Services;

use App\DTO\PaymentDTO;
use App\Entity\Loan;
use App\Entity\LoanState;
use App\Entity\Payment;
use App\Entity\PaymentState;
use App\Factory\PaymentFactory;
use App\Repository\LoanRepository;
use App\Services\PaymentEventDispatcher;
use App\Services\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Contracts\Loggers\LoggerInterface;

final class PaymentServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private LoggerInterface $logger;
    private LoanRepository $loanRepository;
    private PaymentFactory $paymentFactory;
    private PaymentEventDispatcher $paymentEventDispatcher;
    private PaymentService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->loanRepository = $this->createMock(LoanRepository::class);
        $this->paymentFactory = $this->createMock(PaymentFactory::class);
        $this->paymentEventDispatcher = $this->createStub(PaymentEventDispatcher::class);

        $this->service = new PaymentService(
            $this->em,
            $this->logger,
            $this->loanRepository,
            $this->paymentFactory,
            $this->paymentEventDispatcher,
        );
    }

    public function testCreatePaymentExactMatchReturnsPaymentAndMarksLoanPaid(): void
    {
        $dto = new PaymentDTO();
        $dto->amount = '100.00';
        $dto->loanNumber = 'LN100';

        $loan = $this->createMock(Loan::class);
        $loan->method('getAmountToPay')->willReturn('100.00');

        $payment = new Payment();

        $this->loanRepository
            ->expects($this->once())
            ->method('getByReference')
            ->with('LN100')
            ->willReturn($loan);

        $this->paymentFactory
            ->expects($this->once())
            ->method('fromDto')
            ->with($dto, $loan, $dto->amount, $this->isInstanceOf(PaymentState::class))
            ->willReturn($payment);

        // Expect loan to be marked as paid
        $loan->expects($this->once())->method('setState')->with(LoanState::PAID);

        // Ensure transactional wrapper executes callable and flush is called
        $this->em->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($cb) {
                return $cb();
            });

        $this->em->expects($this->once())->method('flush');

        $result = $this->service->createPayment($dto);

        $this->assertSame($payment, $result);
    }

    public function testCreatePaymentPartialReturnsPaymentAndDoesNotMarkLoanPaid(): void
    {
        $dto = new PaymentDTO();
        $dto->amount = '30.00';
        $dto->loanNumber = 'LN200';

        $loan = $this->createMock(Loan::class);
        $loan->method('getAmountToPay')->willReturn('100.00');

        $payment = $this->createMock(Payment::class);

        $this->loanRepository
            ->expects($this->once())
            ->method('getByReference')
            ->with('LN200')
            ->willReturn($loan);

        $this->paymentFactory
            ->expects($this->once())
            ->method('fromDto')
            ->with($dto, $loan, $dto->amount, $this->isInstanceOf(PaymentState::class))
            ->willReturn($payment);

        // Partial payment should not mark loan as paid
        $loan->expects($this->never())->method('setState');

        $this->em->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(fn($cb) => $cb());

        $this->em->expects($this->once())->method('flush');

        $result = $this->service->createPayment($dto);

        $this->assertSame($payment, $result);
    }

    public function testCreatePaymentOverpaymentCreatesRefundAndMarksLoanPaidAndLogs(): void
    {
        $dto = new PaymentDTO();
        $dto->amount = '150.00';
        $dto->loanNumber = 'LN300';

        $loan = $this->createMock(Loan::class);
        $loan->method('getAmountToPay')->willReturn('100.00');
        $loan->method('getReference')->willReturn('LN300');

        $payment = $this->createMock(Payment::class);
        $refund = $this->createMock(Payment::class);

        $this->loanRepository
            ->expects($this->once())
            ->method('getByReference')
            ->with('LN300')
            ->willReturn($loan);

        $this->paymentFactory
            ->expects($this->once())
            ->method('fromDto')
            ->with($dto, $loan, $dto->amount, $this->isInstanceOf(PaymentState::class))
            ->willReturn($payment);

        $refundAmount = '50.00';
        $this->paymentFactory
            ->expects($this->once())
            ->method('createRefund')
            ->with($dto, $loan, $refundAmount)
            ->willReturn($refund);

        // Expect loan marked as paid
        $loan->expects($this->once())->method('setState')->with(LoanState::PAID);

        // Logger should record overpayment info
        $this->logger->expects($this->once())->method('info')->with(
            'Overpayment detected',
            $this->callback(fn($arr) => isset($arr['loanNumber']) && $arr['loanNumber'] === 'LN300')
        );

        $this->em->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(fn($cb) => $cb());

        $this->em->expects($this->once())->method('flush');

        $result = $this->service->createPayment($dto);

        $this->assertSame($payment, $result);
    }
}
