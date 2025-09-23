<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Twig\Environment;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception, DebugLoggerInterface $logger = null, Environment $twig): Response
    {
        $statusCode = $exception->getStatusCode();
        
        // Log the error for debugging purposes
        if ($logger) {
            $logger->error($exception->getMessage(), [
                'exception' => $exception,
                'request' => $exception->getTraceAsString()
            ]);
        }

        // For React app, always serve the React app and let React Router handle the error
        // This ensures all errors are handled by React instead of Twig templates
        return $this->render('react_app.html.twig', [
            'status_code' => $statusCode,
            'status_text' => $exception->getStatusText(),
            'exception' => $exception,
        ], null, $statusCode);
    }
}
