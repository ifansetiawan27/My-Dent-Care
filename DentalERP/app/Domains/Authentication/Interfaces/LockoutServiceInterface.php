<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Interfaces;

/**
 * LockoutServiceInterface
 *
 * Defines the brute-force protection contracts. Tracks failed login
 * attempts per identifier + IP address combination and enforces
 * lockout thresholds. Implementations must be storage-agnostic
 * (Redis, cache driver, or database-backed).
 */
interface LockoutServiceInterface
{
    /**
     * Record a failed authentication attempt.
     *
     * @param string $identifier Username or email used in the attempt.
     * @param string $ipAddress  Client IP address.
     */
    public function recordFailure(string $identifier, string $ipAddress): void;

    /**
     * Determine whether the given identifier + IP is currently locked out.
     *
     * @param  string $identifier Username or email.
     * @param  string $ipAddress  Client IP address.
     * @return bool
     */
    public function isLocked(string $identifier, string $ipAddress): bool;

    /**
     * Clear all failure records for the given identifier + IP combination.
     * Called after a successful authentication.
     *
     * @param string $identifier Username or email.
     * @param string $ipAddress  Client IP address.
     */
    public function clear(string $identifier, string $ipAddress): void;

    /**
     * Get the number of remaining attempts before lockout.
     *
     * @param  string $identifier Username or email.
     * @param  string $ipAddress  Client IP address.
     * @return int
     */
    public function remainingAttempts(string $identifier, string $ipAddress): int;
}
