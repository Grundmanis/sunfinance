<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;
use function FastRoute\simpleDispatcher;

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../src/Container/container.php';

$request = Request::createFromGlobals();
$httpMethod = $request->getMethod();
$uri = $request->getPathInfo();

$routesCallback = require __DIR__ . '/../config/routes.php';
$dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use ($routesCallback) {
    $routesCallback($routeCollector);
});

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

try {
    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $response = new Response('Not Found', 404);
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $response = new Response('Method Not Allowed', 405);
            break;
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            // for this syntax - [PaymentController::class, 'getPaymentStatus']
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $controller = $container->get($class);
                $response = $controller->$method($request);
            } else {
                // to write logic in the route directly
                $response = $handler($request);
            }

            if (!$response instanceof Response) {
                $response = new Response((string)$response);
            }
            break;
        default:
            $response = new Response('Unexpected Error', 500);
            break;
    }
} catch (\Exception $e) {
    $response = new Response('Error: ' . $e->getMessage(), 500);
}

$response->send();
