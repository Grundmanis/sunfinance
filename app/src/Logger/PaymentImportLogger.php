<?php

namespace App\Logger;

class PaymentImportLogger extends MainLogger
{
    public function __construct()
    {
        parent::__construct('payment_import', 'logs/payment_import.log');
    }
}
