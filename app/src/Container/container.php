<?php

use App\Contracts\Loggers\LoggerInterface;
use App\Logger\MonoLogger;
use DI\ContainerBuilder;

return (function () {
    $builder = new ContainerBuilder();

    $builder->addDefinitions([
        LoggerInterface::class => DI\autowire(MonoLogger::class),
    ]);

    return $builder->build();
})();
