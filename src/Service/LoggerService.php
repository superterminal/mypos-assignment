<?php

namespace App\Service;

class LoggerService
{
    private string $logDir;

    public function __construct(string $projectDir)
    {
        $this->logDir = $projectDir . '/var/log';
        
        // Create log directory if it doesn't exist
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        $logFile = $this->logDir . '/' . strtolower($level) . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function logException(\Exception $exception, string $message = 'Exception occurred'): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        $this->error($message, $context);
    }
}
