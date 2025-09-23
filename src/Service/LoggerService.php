<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class LoggerService
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function logApiRequest(string $endpoint, Request $request, ?array $response = null, ?\Exception $exception = null): void
    {
        $context = [
            'endpoint' => $endpoint,
            'method' => $request->getMethod(),
            'ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'timestamp' => new \DateTime(),
        ];

        if ($response) {
            $context['response_status'] = $response['status'] ?? 'unknown';
        }

        if ($exception) {
            $context['exception'] = $exception->getMessage();
            $context['exception_trace'] = $exception->getTraceAsString();
            $this->logger->error('API request failed', $context);
        } else {
            $this->logger->info('API request completed', $context);
        }
    }

    public function logUserAction(string $action, ?string $userId = null, array $context = []): void
    {
        $logContext = array_merge([
            'action' => $action,
            'user_id' => $userId,
            'timestamp' => new \DateTime(),
        ], $context);

        $this->logger->info('User action: ' . $action, $logContext);
    }

    public function logSecurityEvent(string $event, array $context = []): void
    {
        $logContext = array_merge([
            'security_event' => $event,
            'timestamp' => new \DateTime(),
        ], $context);

        $this->logger->warning('Security event: ' . $event, $logContext);
    }
}