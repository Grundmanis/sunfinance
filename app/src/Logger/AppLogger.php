<?php

namespace App\Logger;

final class AppLogger extends MainLogger
{
    public function __construct()
    {
        parent::__construct('app');
    }
}
