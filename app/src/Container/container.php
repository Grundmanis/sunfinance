<?php

use App\Contracts\Loggers\LoggerInterface;
use App\Contracts\Services\CsvReaderInterface;
use App\Logger\AppLogger;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;

return (function () {
    $builder = new ContainerBuilder();

    $builder->addDefinitions([
        LoggerInterface::class => DI\autowire(AppLogger::class),
        CsvReaderInterface::class => DI\autowire(App\Services\CsvReader::class),
        EntityManagerInterface::class => DI\factory(function () {
            return require __DIR__ . '/../../config/db.php';
        }),
    ]);

    return $builder->build();
})();
