<?php

declare(strict_types=1);

namespace App\Platform\Logging\Services;

use App\Platform\Logging\Contracts\LoggerServiceInterface;
use App\Platform\Logging\Enums\LogLevel;
use App\Platform\Logging\Models\SystemLog;
use Illuminate\Support\Facades\Log;

final class LoggerService implements LoggerServiceInterface
{
    public function log(LogLevel $level, string $message, array $context = []): void
    {
        $this->writeToFile($level, $message, $context);

        if ($level->shouldPersist()) {
            SystemLog::create([
                'id'              => SystemLog::newUuid(),
                'level'           => $level->value,
                'message'         => $message,
                'context'         => $context,
                'channel'         => $this->extractChannel($message),
                'organization_id' => $context['organization_id'] ?? null,
                'branch_id'       => $context['branch_id'] ?? null,
                'user_id'         => $context['user_id'] ?? null,
                'exception_class' => $context['exception'] ?? null,
                'file'            => $context['file'] ?? null,
                'line'            => $context['line'] ?? null,
                'trace'           => $context['trace'] ?? null,
                'ip_address'      => $context['ip_address'] ?? null,
                'created_at'      => now(),
            ]);
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
        if (config('app.env') === 'production') {
            return;
        }

        $this->log(LogLevel::Debug, $message, $context);
    }

    private function writeToFile(LogLevel $level, string $message, array $context): void
    {
        $channel = $this->extractChannel($message);

        match ($level) {
            LogLevel::Emergency => Log::emergency($message, ['channel' => $channel, ...$context]),
            LogLevel::Alert     => Log::alert($message, ['channel' => $channel, ...$context]),
            LogLevel::Critical  => Log::critical($message, ['channel' => $channel, ...$context]),
            LogLevel::Error     => Log::error($message, ['channel' => $channel, ...$context]),
            LogLevel::Warning   => Log::warning($message, ['channel' => $channel, ...$context]),
            LogLevel::Notice    => Log::notice($message, ['channel' => $channel, ...$context]),
            LogLevel::Info      => Log::info($message, ['channel' => $channel, ...$context]),
            LogLevel::Debug     => Log::debug($message, ['channel' => $channel, ...$context]),
        };
    }

    private function extractChannel(string $message): string
    {
        if (preg_match('/^\[(\w+)::/', $message, $matches)) {
            return $matches[1];
        }

        return 'system';
    }
}
