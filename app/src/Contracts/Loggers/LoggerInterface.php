<?php

namespace App\Contracts\Loggers;

interface LoggerInterface
{
    public function warning(string $message, array $context = []): void;

    public function error(string $message, array $context = []): void;
}
