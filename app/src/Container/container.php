<?php

use App\Communication\Email\SmtpEmailSender;
use App\Communication\Sms\WhateverSmsSender;
use App\Contracts\Communication\EmailSenderInterface;
use App\Contracts\Communication\SmsSenderInterface;
use App\Contracts\Loggers\LoggerInterface;
use App\Contracts\Services\CsvReaderInterface;
use App\Entity\Loan;
use App\Entity\Payment;
use App\Logger\AppLogger;
use App\Repository\LoanRepository;
use App\Repository\PaymentRepository;
use App\Services\CsvReader;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

return (function () {
    $builder = new ContainerBuilder();

    $builder->addDefinitions([
        LoggerInterface::class => DI\autowire(AppLogger::class),
        CsvReaderInterface::class => DI\autowire(CsvReader::class),
        EntityManagerInterface::class => DI\factory(function () {
            return require __DIR__ . '/../../config/db.php';
        }),
        EventDispatcherInterface::class => DI\factory(function (ContainerInterface $container) {
            $dispatcher = new EventDispatcher();

            $registerEvents = require __DIR__ . '/../../config/events.php';
            $registerEvents($dispatcher, $container);

            return $dispatcher;
        }),
        EmailSenderInterface::class => DI\autowire(SmtpEmailSender::class),
        SmsSenderInterface::class => DI\autowire(WhateverSmsSender::class),
        LoanRepository::class => DI\factory(function (EntityManagerInterface $em) {
            return $em->getRepository(Loan::class);
        }),
        PaymentRepository::class => DI\factory(function (EntityManagerInterface $em) {
            return $em->getRepository(Payment::class);
        }),
        ValidatorInterface::class => DI\factory(function () {
            return \Symfony\Component\Validator\Validation::createValidatorBuilder()
                ->enableAttributeMapping()
                ->getValidator();
        }),
    ]);

    return $builder->build();
})();
