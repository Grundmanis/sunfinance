<?php

use App\Controllers\PaymentController;
use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->post('/api/payment', [PaymentController::class, 'create']);
};
