<?php

declare(strict_types=1);

namespace App\Platform\Logging\Contracts;

use App\Platform\Logging\Enums\LogLevel;

/**
 * LoggerServiceInterface
 *
 * The single contract for structured logging across the entire ERP.
 * Implementations route logs by level: file (always), database (>= warning),
 * external monitoring (>= error). Persistence is non-blocking via Queue.
 *
 * Platform rule: Domains depend on this interface only — never on Laravel Log directly.
 *
 * Message format convention: "[Module::action] message"
 */
interface LoggerServiceInterface
{
    /**
     * Log a message at an explicit level with structured context.
     *
     * @param  LogLevel             $level
     * @param  string               $message
     * @param  array<string, mixed> $context
     * @return void
     */
    public function log(LogLevel $level, string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function alert(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function critical(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function notice(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void;

    /**
     * @param  array<string, mixed> $context
     */
    public function debug(string $message, array $context = []): void;
}
