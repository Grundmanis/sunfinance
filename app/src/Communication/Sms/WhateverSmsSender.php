<?php

namespace App\Communication\Sms;

use App\Contracts\Communication\SmsSenderInterface;

final class WhateverSmsSender implements SmsSenderInterface
{
    public function send(string $phone, string $message): void
    {
        // echo "sms actually sent with message: $message \n";
    }
}
