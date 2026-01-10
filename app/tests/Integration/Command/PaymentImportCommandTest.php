<?php

namespace Tests\Integration\Commands;

use App\Commands\PaymentImportCommand;
use App\Logger\PaymentImportLogger;
use App\Normalization\Csv\PaymentNormalizer;
use App\Services\PaymentService;
use App\Validation\PaymentValidator;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Contracts\Services\CsvReaderInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PaymentImportCommandTest extends KernelTestCase
{
    private PaymentImportCommand $command;
    private CommandTester $commandTester;
    private PaymentValidator $validator;
    private PaymentNormalizer $normalizer;
    private CsvReaderInterface $csvReader;
    private PaymentImportLogger $logger;
    private EventDispatcherInterface $eventDispatcher;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        $this->validator = $this->createStub(PaymentValidator::class);
        $this->normalizer = new PaymentNormalizer();
        $this->csvReader = $this->createStub(CsvReaderInterface::class);
        $this->logger = $this->createStub(PaymentImportLogger::class);
        $this->eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->paymentService = $this->createStub(PaymentService::class);

        $this->command = new PaymentImportCommand(
            $this->validator,
            $this->normalizer,
            $this->csvReader,
            $this->logger,
            $this->eventDispatcher,
            $this->paymentService
        );

        $this->commandTester = new CommandTester($this->command);
    }

    public function testFileNotExists(): void
    {
        $filePath = __DIR__ . '/non_existing.csv';
        $exitCode = $this->commandTester->execute(['file' => $filePath]);

        $this->assertEquals(PaymentImportCommand::FILE_NOT_EXISTS, $exitCode);
    }

    public function testValidationError(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'payments');
        file_put_contents($filePath, "loanNumber,refId,amount,paymentDate\nLN123,PAY123,100,2026-01-10");

        $this->csvReader->method('setFilePath')->with($filePath);
        $this->csvReader->method('getRecords')->willReturn([
            ['loanNumber' => 'LN123', 'refId' => 'PAY123', 'amount' => 100, 'paymentDate' => '2026-01-10']
        ]);

        $this->validator->method('validate')->willReturn(new \App\Validation\ValidationResult(false, [
            ['propertyPath' => 'loanNumber', 'invalidValue' => null, 'message' => 'Loan not found', 'type' => 'not_found']
        ]));

        $exitCode = $this->commandTester->execute(['file' => $filePath]);

        $this->assertEquals(PaymentImportCommand::MISSING_LOAN_NUMBER, $exitCode);

        unlink($filePath);
    }

    public function testSuccessfulImport(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'payments');
        file_put_contents($filePath, "loanNumber,refId,amount,paymentDate\nLN123,PAY123,100,2026-01-10");

        $this->csvReader
            ->method('setFilePath')
            ->with($filePath);

        $this->csvReader
            ->method('getRecords')
            ->willReturn([
                ['loanNumber' => 'LN123', 'refId' => 'PAY123', 'amount' => 100, 'paymentDate' => '2026-01-10']
            ]);

        $this->validator
            ->method('validate')
            ->willReturn(new \App\Validation\ValidationResult(true, []));


        $exitCode = $this->commandTester->execute(['file' => $filePath]);

        $this->assertEquals(PaymentImportCommand::SUCCESS, $exitCode);

        unlink($filePath);
    }

    // TODO: implement other tests
}
