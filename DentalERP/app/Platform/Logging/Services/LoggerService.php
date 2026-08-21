<?php

declare(strict_types=1);

namespace App\Platform\Logging\Services;

use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Logging\Enums\LogLevel;
use App\Platform\Logging\Jobs\LogJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * LoggerService
 *
 * Concrete implementation of LoggerServiceInterface.
 * Routes logs by level: file (always), database (>= warning), external (>= error).
 * Database persistence is non-blocking via Queue.
 */
final class LoggerService implements LoggerServiceInterface
{
    public function log(LogLevel $level, string $message, array $context = []): void
    {
        // Always log to file (Laravel's default channel)
        $this->logToFile($level, $message, $context);

        // Log to database for warning and above
        if ($this->shouldLogToDatabase($level)) {
            $this->logToDatabase($level, $message, $context);
        }
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::Emergency, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::Alert, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::Critical, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::Error, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::Warning, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::Notice, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::Info, $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::Debug, $message, $context);
    }

    private function logToFile(LogLevel $level, string $message, array $context): void
    {
        Log::{$level->value}($message, $context);
    }

    private function logToDatabase(LogLevel $level, string $message, array $context): void
    {
        $user = Auth::user();
        $request = request();
        
        $exception = $context['exception'] ?? null;

        dispatch(new LogJob(
            level: $level,
            message: $message,
            context: $context,
            organizationId: $user?->organization_id ?? $request->input('organization_id'),
            userId: $user?->id,
            exceptionClass: $exception ? get_class($exception) : null,
            exceptionMessage: $exception?->getMessage(),
            exceptionTrace: $exception?->getTraceAsString(),
            requestId: $request->header('X-Request-ID') ?? \Illuminate\Support\Str::uuid()->toString(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            method: $request->method(),
            url: $request->fullUrl(),
        ));
    }

    private function shouldLogToDatabase(LogLevel $level): bool
    {
        $databaseLevels = [
            LogLevel::Emergency,
            LogLevel::Alert,
            LogLevel::Critical,
            LogLevel::Error,
            LogLevel::Warning,
        ];

        return in_array($level, $databaseLevels, true);
    }
}
