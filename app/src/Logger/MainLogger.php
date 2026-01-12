<?php

namespace App\Logger;

use Monolog\Logger;
use App\Contracts\Loggers\LoggerInterface;
use App\Factory\LoggerFactory;

abstract class MainLogger implements LoggerInterface
{
    protected Logger $logger;
    private string $category;

    public function __construct(string $category, string $logPath = "logs/app.log")
    {
        // Or category can be used to create different loggers
        $this->logger = LoggerFactory::createLogger('app', $logPath);
        $this->category = $category;
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->warning("[$this->category] $message", $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning("[$this->category] $message", $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error("[$this->category] $message", $context);
    }
}
