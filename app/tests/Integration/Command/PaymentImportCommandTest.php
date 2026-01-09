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
    private string $columns = "paymentDate,payerName,payerSurname,amount,nationalSecurityNumber,description,paymentReference";
    private array $validDataArray = ['paymentDate' => '2023-10-01', 'payerName' => 'John', 'payerSurname' => 'Doe', 'amount' => '100.50', 'nationalSecurityNumber' => '123456789', 'description' => 'Loan number LN12345678', 'paymentReference' => 'REF123'];

    protected function setUp(): void
    {
        $this->testCsvPath = sys_get_temp_dir() . '/test.csv';
        $validData = implode(',', array_values($this->validDataArray));
        file_put_contents($this->testCsvPath, "$this->columns\n$validData");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testCsvPath)) {
            unlink($this->testCsvPath);
        }
    }

    public function testExecuteWithValidCsv(): void
    {
        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

        $input = new ArrayInput(['file' => $this->testCsvPath]);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(0, $result);
    }

    public function testExecuteWithNegativeAmount(): void
    {
        $data = implode(',', array_merge($this->validDataArray, ['amount' => '-50.00']));
        file_put_contents($this->testCsvPath, "$this->columns\n$data");

        $csvReader = new CsvReader();
        $normalizer = new PaymentNormalizer(new \App\Transformers\DateTransformer());
        $validator = new PaymentValidator();

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

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

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

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

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

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

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

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

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

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

        $command = new PaymentImportCommand($validator, $normalizer, $csvReader);

        $input = new ArrayInput(['file' => 'randomFilePath']);
        $output = new BufferedOutput();

        $result = $command->run($input, $output);

        $this->assertEquals(6, $result);
    }
}
