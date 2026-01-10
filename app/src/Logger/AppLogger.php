<?php

namespace App\Logger;

class AppLogger extends MainLogger
{
    public function __construct()
    {
        parent::__construct('app', 'logs/app.log');
    }
}
