<?php

namespace App\Controllers;

use App\Contracts\Loggers\LoggerInterface;
use App\Normalization\Api\PaymentNormalizer;
use App\Services\PaymentService;
use App\Validation\PaymentValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PaymentController
{
    private LoggerInterface $logger;
    private PaymentNormalizer $paymentNormalizer;
    private PaymentService $paymentService;
    private PaymentValidator $validator;

    public function __construct(
        LoggerInterface $logger,
        PaymentNormalizer $paymentNormalizer,
        PaymentService $paymentService,
        PaymentValidator $validator
    ) {
        $this->logger = $logger;
        $this->paymentNormalizer = $paymentNormalizer;
        $this->paymentService = $paymentService;
        $this->validator = $validator;
    }

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $dto = $this->paymentNormalizer->normalize($data);
        $validationResult = $this->validator->validate($dto);

        if (!$validationResult->isValid()) {
            $this->logger->info('Payment validation failed', ['data' => $validationResult]);
            return $this->mapErrorToExitCode($validationResult->getErrors()[0]);
        }

        $payment = $this->paymentService->createPayment($dto);

        return new JsonResponse($this->paymentNormalizer->denormalize($payment), 201);
    }

    // TODO: move out
    private function mapErrorToExitCode(array $error): JsonResponse
    {
        if ($error['type'] === 'duplicate') { // TODO: const
            return new JsonResponse(['error' => $error['message']], 409);
        }
        return new JsonResponse(['error' => $error['message']], 400);
    }
}
