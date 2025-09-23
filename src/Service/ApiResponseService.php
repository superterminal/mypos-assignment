<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ApiResponseService
{
    public function success(array $data = [], int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = ['success' => true];
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        
        return new JsonResponse($response, $statusCode);
    }

    public function error(string $message, int $statusCode = Response::HTTP_BAD_REQUEST, array $details = []): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        return new JsonResponse($response, $statusCode);
    }

    public function validationErrors(ConstraintViolationListInterface $violations): JsonResponse
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }
        
        return $this->error('Validation failed', Response::HTTP_BAD_REQUEST, ['errors' => $errors]);
    }

    public function notFound(string $resource = 'Resource'): JsonResponse
    {
        return $this->error($resource . ' not found', Response::HTTP_NOT_FOUND);
    }

    public function forbidden(string $message = 'Access denied'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    public function unauthorized(string $message = 'Authentication required'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    public function created(array $data = []): JsonResponse
    {
        return $this->success($data, Response::HTTP_CREATED);
    }

    public function noContent(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
