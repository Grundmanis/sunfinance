<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiErrorResponder
{
    public static function toResponse(array $error): JsonResponse
    {
        $type = $error['type'] ?? 'validation';
        $message = $error['message'] ?? 'Unknown error';
        switch ($type) {
            case 'duplicate':
                $status = 409;
                $payload = ['error' => $message];
                break;
            case 'notFound':
            case 'not_found':
                $status = 404;
                $payload = ['error' => $message];
                break;
            case 'validation':
            default:
                $property = $error['propertyPath'] ?? null;
                $text = $property ? "[$property] $message" : $message;
                $status = 400;
                $payload = ['error' => $text];
                break;
        }

        return new JsonResponse($payload, $status);
    }
}
