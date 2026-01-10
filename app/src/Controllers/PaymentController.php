<?php

namespace App\Controllers;

use App\Contracts\Loggers\LoggerInterface;
use App\Normalization\Api\PaymentNormalizer;
use App\Services\PaymentService;
use App\Validation\PaymentValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PaymentController
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

        $payment = $this->paymentNormalizer->normalize($data);
        $validationResult = $this->validator->validate($payment->toArray());

        if (!$validationResult->isValid()) {
            foreach ($validationResult->getErrors() as $error) {
                $this->logger->warning('Validation error', [
                    'field' => $error['propertyPath'],
                    'value' => $error['invalidValue'],
                    'message' => $error['message'],
                ]);
                return $this->mapErrorToExitCode($error);
            }
        }

        $this->paymentService->processPayment($payment);

        return new JsonResponse([
            'status' => 'ok',
            'time' => date('c'),
            'payment' => $payment,
        ]);
    }

    // TODO: move out
    private function mapErrorToExitCode(array $error): JsonResponse
    {
        return new JsonResponse(['error' => $error['message']], 400);
        // throw 409 for the duplicate
    }
}
