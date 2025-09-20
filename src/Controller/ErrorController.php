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

        // Determine which template to use based on status code
        $template = match ($statusCode) {
            404 => 'bundles/TwigBundle/Exception/error404.html.twig',
            default => 'bundles/TwigBundle/Exception/error.html.twig'
        };

        return new Response(
            $twig->render($template, [
                'status_code' => $statusCode,
                'status_text' => $exception->getStatusText(),
                'exception' => $exception,
            ]),
            $statusCode
        );
    }
}
