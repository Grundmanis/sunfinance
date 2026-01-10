<?php

namespace App\Communication\Email;

use App\Contracts\Communication\EmailSenderInterface;

final class SmtpEmailSender implements EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // echo "mail actually sent with body: $body \n";
    }
}
