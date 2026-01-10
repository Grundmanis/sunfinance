<?php

namespace App\Logger;

use Monolog\Logger;
use App\Contracts\Loggers\LoggerInterface;
use App\Factory\LoggerFactory;

abstract class MainLogger implements LoggerInterface
{
    protected Logger $logger;

    public function __construct(string $category, string $logPath)
    {
        $this->logger = LoggerFactory::createLogger($category, $logPath);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}
