<?php

namespace App\Contracts\Normalization;

use App\DTO\PaymentDTO;

interface PaymentNormalizerInterface
{
    public function normalize(array $raw): PaymentDTO;
}
