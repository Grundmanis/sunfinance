<?php

use App\Listener\FailedPaymentListener;
use App\Listener\LoanPaidEmailListener;
use App\Listener\LoanPaidSmsListener;
use App\Listener\PaymentReceivedEmailListener;
use App\Listener\PaymentReceivedSmsListener;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

return function (
    EventDispatcher $dispatcher,
    ContainerInterface $container
): void {
    // TODO: use event consts instead of strings
    $dispatcher->addListener(
        'payment.received',
        $container->get(PaymentReceivedEmailListener::class)
    );

    $dispatcher->addListener(
        'payment.received',
        $container->get(PaymentReceivedSmsListener::class)
    );

    $dispatcher->addListener(
        'loan.fully_paid',
        $container->get(LoanPaidEmailListener::class)
    );

    $dispatcher->addListener(
        'loan.fully_paid',
        $container->get(LoanPaidSmsListener::class)
    );

    $dispatcher->addListener(
        'payments.failed_report',
        $container->get(FailedPaymentListener::class)
    );
};
