<?php

namespace App\Logger;

final class PaymentImportLogger extends MainLogger
{
    public function __construct()
    {
        parent::__construct('payment_import');
    }
}
