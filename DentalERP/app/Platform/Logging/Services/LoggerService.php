<?php

declare(strict_types=1);

namespace App\Platform\Logging\Services;

use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Logging\Enums\LogLevel;
use App\Platform\Logging\Jobs\LogJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

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
        // Debug noise is suppressed entirely in production.
        if ($level === LogLevel::Debug && config('app.env') === 'production') {
            return;
        }

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

        $exceptionClass = match (true) {
            $exception instanceof Throwable => get_class($exception),
            is_string($exception) && $exception !== '' => $exception,
            default => null,
        };

        dispatch(new LogJob(
            level: $level,
            message: $message,
            context: $context,
            channel: $this->extractChannel($message),
            organizationId: $context['organization_id'] ?? $user?->organization_id ?? $request->input('organization_id'),
            branchId: $context['branch_id'] ?? $user?->branch_id,
            userId: $context['user_id'] ?? $user?->id,
            exceptionClass: $exceptionClass,
            exceptionMessage: $exception instanceof Throwable ? $exception->getMessage() : null,
            file: $context['file'] ?? ($exception instanceof Throwable ? $exception->getFile() : null),
            line: isset($context['line']) ? (int) $context['line'] : ($exception instanceof Throwable ? $exception->getLine() : null),
            trace: $context['trace'] ?? ($exception instanceof Throwable ? $exception->getTraceAsString() : null),
            requestId: $request->header('X-Request-ID') ?? Str::uuid()->toString(),
            ipAddress: $context['ip_address'] ?? $request->ip(),
            userAgent: $request->userAgent(),
            method: $request->method(),
            url: $request->fullUrl(),
        ));
    }

    /**
     * Extract the emitting module from a "[Module::action] message" prefix.
     * Falls back to "system" when no prefix is present.
     */
    private function extractChannel(string $message): string
    {
        if (preg_match('/^\[([A-Za-z0-9_]+)(?:::[A-Za-z0-9_]+)?\]/', $message, $matches) === 1) {
            return $matches[1];
        }

        return 'system';
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
