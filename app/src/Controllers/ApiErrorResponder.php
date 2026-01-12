<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;

final class ApiErrorResponder
{
    public static function toResponse(array $error): JsonResponse
    {
        $type = $error['type'] ?? 'validation';
        return match ($type) {
            'duplicate' => new JsonResponse(['error' => $error['message']], 409),
            'notFound' => new JsonResponse(['error' => $error['message']], 400),
            'validation' => new JsonResponse(['error' => $error['message']], 400),
            default => new JsonResponse(['error' => $error['message']], 400),
        };
    }
}
