<?php

namespace Tests\Integration\Commands;

use App\Commands\PaymentImportCommand;
use App\Services\CsvReader;
use App\Normalization\Csv\PaymentNormalizer;
use App\Validation\PaymentValidator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\TestCase;

class PaymentImportCommandTest extends TestCase
{
    private string $testCsvPath;

    protected function setUp(): void
    {
        // Create a temporary CSV file for testing
        $this->testCsvPath = sys_get_temp_dir() . '/test.csv';
        file_put_contents($this->testCsvPath, "paymentDate,payerName,payerSurname,amount,nationalSecurityNumber,description,paymentReference\n2023-10-01,John,Doe,100.50,123456789,Loan number LNAB12345678,REF123");
    }

    protected function tearDown(): void
    {
        // Remove the temporary CSV file after the test
        if (file_exists($this->testCsvPath)) {
            unlink($this->testCsvPath);
        }
    }

    public function testExecuteWithValidCsv(): void
    {
        // Arrange: Set up dependencies
        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

        // Simulate console input and output
        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        // Act: Execute the command
        $result = $command->run($input, $output);

        // Assert: Verify the output and result
        $this->assertEquals(0, $result); // Command::SUCCESS
        $outputContent = $output->fetch();
        $this->assertStringContainsString('All records are valid!', $outputContent);
    }

    public function testExecuteWithInvalidCsv(): void
    {
        // Arrange: Create an invalid CSV file
        file_put_contents($this->testCsvPath, "paymentDate,payerName,payerSurname,amount,nationalSecurityNumber,description,paymentReference\ninvalid-date,John,Doe,-50,,Invalid description,REF123");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        // Act: Execute the command
        $result = $command->run($input, $output);

        // Assert: Verify the output and result
        $this->assertEquals(1, $result); // Command::FAILURE
        $outputContent = $output->fetch();
        $this->assertStringContainsString('This value is not a valid date.', $outputContent);
        $this->assertStringContainsString('This value should be greater than 0.', $outputContent);
    }
}
