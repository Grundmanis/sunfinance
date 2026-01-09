<?php

namespace Tests\Unit\Services;

use App\Services\CsvReader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    private CsvReader $csvReader;

    protected function setUp(): void
    {
        $this->csvReader = new CsvReader();
    }

    // public function testGetRecordsWithValidFile(): void
    // {
    //     // Arrange: Create a temporary CSV file
    //     $filePath = sys_get_temp_dir() . '/test.csv';
    //     file_put_contents($filePath, "header1,header2,header3\nvalue1,value2,value3");

    //     $this->csvReader->setFilePath($filePath);

    //     // Act: Get records
    //     $records = $this->csvReader->getRecords();

    //     // Assert: Verify the records
    //     $this->assertIsIterable($records);
    //     $recordsArray = iterator_to_array($records);
    //     $this->assertCount(1, $recordsArray);
    //     $this->assertEquals(['header1' => 'value1', 'header2' => 'value2', 'header3' => 'value3'], $recordsArray[0]);

    //     // Cleanup: Remove the temporary file
    //     unlink($filePath);
    // }

    public function testGetRecordsWithNonExistentFile(): void
    {
        // Arrange: Set a non-existent file path
        $filePath = '/path/to/nonexistent/file.csv';
        $this->csvReader->setFilePath($filePath);

        // Assert: Expect an exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("File not found: $filePath");

        // Act: Attempt to get records
        $this->csvReader->getRecords();
    }
}
