<?php

namespace App\Contracts\Normalization;

interface PaymentNormalizerInterface
{
    public function normalize(array $raw): array;
}
