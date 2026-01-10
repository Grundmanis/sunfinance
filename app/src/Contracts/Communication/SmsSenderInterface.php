<?php

namespace App\Contracts\Communication;

interface SmsSenderInterface
{
    public function send(
        string $phone,
        string $message
    ): void;
}
