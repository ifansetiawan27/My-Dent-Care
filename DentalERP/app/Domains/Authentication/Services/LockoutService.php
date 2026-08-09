<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Services;

use App\Domains\Authentication\Interfaces\LockoutServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * LockoutService
 *
 * Implements brute-force protection via Redis-backed counters.
 * Tracks failed login attempts per identifier + IP address combination
 * and enforces a 5-attempt lockout with a 15-minute TTL window.
 *
 * Layer rule: No database access. Redis/persistence via Cache facade only.
 */
class LockoutService implements LockoutServiceInterface
{
    /**
     * Service name used in structured log messages.
     */
    private const SERVICE_NAME = 'LockoutService';

    /**
     * Maximum failed attempts before lockout is enforced.
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * TTL in minutes for the failure counter.
     */
    private const TTL_MINUTES = 15;

    // -------------------------------------------------------------------------
    // LockoutServiceInterface Implementation
    // -------------------------------------------------------------------------

    /**
     * Record a failed authentication attempt.
     * Increments the Redis counter and sets/refreshes the 15-minute TTL.
     */
    public function recordFailure(string $identifier, string $ipAddress): void
    {
        try {
            $key = $this->buildKey($identifier, $ipAddress);

            Cache::store('redis')->increment($key);
            Cache::store('redis')->set(
                $key . ':ttl',
                true,
                now()->addMinutes(self::TTL_MINUTES),
            );

            $this->logInfo('recordFailure', 'Failure recorded.', [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
            ]);
        } catch (Throwable $e) {
            $this->logError('recordFailure', $e, [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
            ]);
        }
    }

    /**
     * Determine whether the given identifier + IP is currently locked out.
     */
    public function isLocked(string $identifier, string $ipAddress): bool
    {
        try {
            $key = $this->buildKey($identifier, $ipAddress);
            $count = (int) Cache::store('redis')->get($key, 0);

            return $count >= self::MAX_ATTEMPTS;
        } catch (Throwable $e) {
            $this->logError('isLocked', $e, [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
            ]);

            return false;
        }
    }

    /**
     * Clear all failure records for the given identifier + IP combination.
     * Called after a successful authentication.
     */
    public function clear(string $identifier, string $ipAddress): void
    {
        try {
            $key = $this->buildKey($identifier, $ipAddress);

            Cache::store('redis')->forget($key);
            Cache::store('redis')->forget($key . ':ttl');

            $this->logInfo('clear', 'Lockout counter cleared.', [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
            ]);
        } catch (Throwable $e) {
            $this->logError('clear', $e, [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
            ]);
        }
    }

    /**
     * Get the number of remaining attempts before lockout.
     */
    public function remainingAttempts(string $identifier, string $ipAddress): int
    {
        try {
            $key = $this->buildKey($identifier, $ipAddress);
            $count = (int) Cache::store('redis')->get($key, 0);

            return max(0, self::MAX_ATTEMPTS - $count);
        } catch (Throwable $e) {
            $this->logError('remainingAttempts', $e, [
                'identifier' => $identifier,
                'ip_address' => $ipAddress,
            ]);

            return self::MAX_ATTEMPTS;
        }
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a consistent Redis key for the identifier + IP combination.
     */
    private function buildKey(string $identifier, string $ipAddress): string
    {
        return 'auth:failed:' . $identifier . ':' . $ipAddress;
    }

    // -------------------------------------------------------------------------
    // Private — Logging Helpers
    // -------------------------------------------------------------------------

    /**
     * Log an informational message with structured context.
     *
     * @param  array<string, mixed> $context
     */
    private function logInfo(string $action, string $message, array $context = []): void
    {
        Log::info(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    /**
     * Log a warning message with structured context.
     *
     * @param  array<string, mixed> $context
     */
    private function logWarning(string $action, string $message, array $context = []): void
    {
        Log::warning(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $message,
            ['service' => self::SERVICE_NAME, ...$context],
        );
    }

    /**
     * Log an error with full exception details.
     *
     * @param  array<string, mixed> $context
     */
    private function logError(string $action, Throwable $e, array $context = []): void
    {
        Log::error(
            '[' . self::SERVICE_NAME . '::' . $action . '] ' . $e->getMessage(),
            [
                'service'   => self::SERVICE_NAME,
                'exception' => $e::class,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                ...$context,
            ],
        );
    }
}
