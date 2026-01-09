<?php

namespace App\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use App\Contracts\Loggers\LoggerInterface;

class MonoLogger implements LoggerInterface
{
    protected Logger $logger;

    public function __construct()
    {
        // TODO: make dynamic log level and name
        $log = new Logger('app');
        $log->pushHandler(new StreamHandler('logs/app.log', Level::Warning));

        $this->logger = $log;
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
