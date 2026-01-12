<?php

namespace App\Communication\Email;

use App\Contracts\Communication\EmailSenderInterface;
use App\Contracts\Loggers\LoggerInterface;

final class SmtpEmailSender implements EmailSenderInterface
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function send(string $to, string $subject, string $body): void
    {
        // Simulate sending email via SMTP
        $this->logger->info("Sending email to: $to with subject: $subject");
    }
}
