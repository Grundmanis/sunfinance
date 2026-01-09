<?php

namespace App\Services;

use App\Contracts\Services\CsvReaderInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use InvalidArgumentException;

class CsvReader implements CsvReaderInterface
{
    private string $filePath;
    private int $headerOffset = 0;

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getRecords(): iterable
    {
        if (!file_exists($this->filePath)) {
            throw new InvalidArgumentException("File not found: $this->filePath");
        }

        $csv = Reader::from($this->filePath, 'r');
        $csv->setHeaderOffset($this->headerOffset);
        $csv->setEscape(''); // Required for PHP 8.4+ to avoid deprecation notices

        $stmt = new Statement();

        return $stmt->process($csv);
    }
}
