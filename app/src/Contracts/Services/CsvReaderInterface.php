<?php

namespace App\Contracts\Services;

interface CsvReaderInterface
{
    public function setFilePath(string $filePath): void;
    public function getRecords(): iterable;
}
