<?php

namespace Tests\Integration\Commands;

use App\Commands\PaymentImportCommand;
use App\Services\CsvReader;
use App\Normalization\Csv\PaymentNormalizer;
use App\Validation\PaymentValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentImportCommandTest extends TestCase
{
    private string $testCsvPath;
    private string $columns = "paymentDate,payerName,payerSurname,amount,nationalSecurityNumber,description,paymentReference";
    private array $validDataArray = ['paymentDate' => '2023-10-01', 'payerName' => 'John', 'payerSurname' => 'Doe', 'amount' => '100.50', 'nationalSecurityNumber' => '123456789', 'description' => 'Loan number LN12345678', 'paymentReference' => 'REF123'];

    private $entityManager;
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->testCsvPath = sys_get_temp_dir() . '/test.csv';
        $validData = implode(',', array_values($this->validDataArray));
        file_put_contents($this->testCsvPath, "$this->columns\n$validData");
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createStub(EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testCsvPath)) {
            unlink($this->testCsvPath);
        }
    }

    public function testExecuteWithNegativeAmount(): void
    {
        $data = implode(',', array_merge($this->validDataArray, ['amount' => '-50.00']));
        file_put_contents($this->testCsvPath, "$this->columns\n$data");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();
        $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $this->entityManager, $this->eventDispatcher);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(2, $result);
    }

    public function testExecuteWithDuplicate(): void
    {
        $data = implode(',', array_values($this->validDataArray));
        file_put_contents($this->testCsvPath, "$this->columns\n$data\n$data");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();
        $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $this->entityManager, $this->eventDispatcher);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(1, $result);
    }

    public function testExecuteWithInvalidDate(): void
    {
        $data = implode(',', array_merge($this->validDataArray, ['paymentDate' => 'invalid-date']));
        file_put_contents($this->testCsvPath, "$this->columns\n$data");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();
        $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $this->entityManager, $this->eventDispatcher);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(2, $result);
    }

    public function testExecuteWithMissingLoanNumberInDescription(): void
    {
        $data = implode(',', array_merge($this->validDataArray, ['description' => 'No loan number here']));
        file_put_contents($this->testCsvPath, "$this->columns\n$data");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();
        $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $this->entityManager, $this->eventDispatcher);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(4, $result);
    }

    public function testExecuteWithMissingRef(): void
    {
        $data = implode(',', array_merge($this->validDataArray, ['paymentReference' => '']));
        file_put_contents($this->testCsvPath, "$this->columns\n$data");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();
        $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $this->entityManager, $this->eventDispatcher);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(5, $result);
    }

    public function testExecuteWithMissingFile(): void
    {
        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();
        $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $this->entityManager, $this->eventDispatcher);

        $input = new ArrayInput(['file' => 'randomFilePath']);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(6, $result);
    }

    // public function testExecuteWithValidData(): void
    // {
    //     $csvReader = new CsvReader();
    //     $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
    //     $validator = new PaymentValidator();
    //     $logger = $this->createStub(\App\Logger\PaymentImportLogger::class);

    //     // Mock the EntityManager to simulate database operations
    //     $entityManager = $this->createMock(EntityManagerInterface::class);
    //     $entityManager->expects($this->any())
    //         ->method('getRepository')
    //         ->willReturnCallback(function ($entity) {
    //             if ($entity === 'App\Entity\Loan') {
    //                 $loanMock = $this->createMock(\App\Entity\Loan::class);
    //                 $loanMock->method('getId')->willReturn(1);
    //                 $loanMock->method('getAmountToPay')->willReturn('100.50');
    //                 return $this->createConfiguredMock(\Doctrine\Persistence\ObjectRepository::class, [
    //                     'findOneBy' => $loanMock,
    //                 ]);
    //             }
    //             return null;
    //         });

    //     $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

    //     $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $entityManager, $eventDispatcher);

    //     $input = new ArrayInput(['file' => $this->testCsvPath]);
    //     $output = new BufferedOutput();

    //     $result = $command->run($input, $output);

    //     $this->assertEquals(PaymentImportCommand::SUCCESS, $result);
    // }

    // public function testExecuteWithValidCsv(): void
    // {
    //     $csvReader = new CsvReader();
    //     $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
    //     $validator = new PaymentValidator();
    //     $logger = $this->createMock(\App\Logger\PaymentImportLogger::class);
    //     $entityManager = $this->createMock(EntityManagerInterface::class);
    //     $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

    //     $command = new PaymentImportCommand($validator, $normalizer, $csvReader, $logger, $entityManager, $eventDispatcher);

    //     $input = new ArrayInput(['file' => $this->testCsvPath]);
    //     $output = new BufferedOutput();

    //     $result = $command->run($input, $output);

    //     $this->assertEquals(0, $result);
    // }
}
