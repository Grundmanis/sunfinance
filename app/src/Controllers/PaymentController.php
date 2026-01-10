<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentController
{
    public function create()
    {
        return new JsonResponse([
            'status' => 'ok',
            'time' => date('c'),
        ]);
    }
}
