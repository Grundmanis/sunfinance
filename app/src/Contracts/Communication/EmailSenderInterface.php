<?php

namespace App\Contracts\Communication;

interface EmailSenderInterface
{
    public function send(
        string $to,
        string $subject,
        string $body
    ): void;
}
