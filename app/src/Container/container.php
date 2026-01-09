<?php

use App\Contracts\Loggers\LoggerInterface;
use App\Contracts\Services\CsvReaderInterface;
use App\Logger\AppLogger;
use DI\ContainerBuilder;

return (function () {
    $builder = new ContainerBuilder();

    $builder->addDefinitions([
        LoggerInterface::class => DI\autowire(AppLogger::class),
        CsvReaderInterface::class => DI\autowire(App\Services\CsvReader::class),
    ]);

    return $builder->build();
})();
