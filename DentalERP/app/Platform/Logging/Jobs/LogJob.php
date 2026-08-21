<?php

declare(strict_types=1);

namespace App\Platform\Logging\Jobs;

use App\Platform\Logging\Enums\LogLevel;
use App\Platform\Logging\Models\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * LogJob
 *
 * Asynchronously persists a log entry to the system_logs table.
 * Dispatched by LoggerService for warning-level logs and above.
 */
class LogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly LogLevel $level,
        private readonly string $message,
        private readonly array $context,
        private readonly ?string $organizationId,
        private readonly ?string $userId,
        private readonly ?string $exceptionClass,
        private readonly ?string $exceptionMessage,
        private readonly ?string $exceptionTrace,
        private readonly ?string $requestId,
        private readonly ?string $ipAddress,
        private readonly ?string $userAgent,
        private readonly ?string $method,
        private readonly ?string $url,
    ) {
    }

    public function handle(): void
    {
        SystemLog::create([
            'id' => (string) Str::orderedUuid(),
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
            'level' => $this->level->value,
            'channel' => 'application',
            'message' => $this->message,
            'context' => $this->context,
            'extra' => [],
            'exception_class' => $this->exceptionClass,
            'exception_message' => $this->exceptionMessage,
            'exception_trace' => $this->exceptionTrace,
            'request_id' => $this->requestId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'method' => $this->method,
            'url' => $this->url,
            'created_at' => now(),
        ]);
    }
}
