<?php

namespace App\Communication\Sms;

use App\Contracts\Communication\SmsSenderInterface;
use App\Contracts\Loggers\LoggerInterface;

final class WhateverSmsSender implements SmsSenderInterface
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function send(string $phone, string $message): void
    {
        // Simulate sending SMS via Whatever service
        $this->logger->info("Sending SMS to: $phone with message: $message");
    }
}
