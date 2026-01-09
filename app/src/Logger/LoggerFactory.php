<?php

namespace App\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class LoggerFactory
{
    public static function createLogger(string $category, string $logPath): Logger
    {
        $logger = new Logger($category);
        $logger->pushHandler(new StreamHandler($logPath, Level::Warning));

        return $logger;
    }
}
