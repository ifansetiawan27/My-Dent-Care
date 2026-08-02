<?php

declare(strict_types=1);

namespace App\Platform\Queue\Contracts;

use App\Platform\Queue\Enums\QueuePriority;
use DateTimeInterface;

/**
 * QueueServiceInterface
 *
 * The single contract for dispatching background jobs across the ERP.
 * Abstracts the underlying queue driver (Redis, database, SQS) so domains
 * and platform services remain driver-agnostic.
 *
 * Platform rule: Depend on this interface for cross-cutting async work.
 * Ordinary domain jobs may still use Laravel's dispatch(); this interface
 * exists for platform-level control (priority routing, delayed dispatch).
 */
interface QueueServiceInterface
{
    /**
     * Dispatch a job onto a prioritized queue.
     *
     * @param  object        $job       A queueable job instance.
     * @param  QueuePriority $priority  Target priority lane.
     * @return void
     */
    public function dispatch(object $job, QueuePriority $priority = QueuePriority::Default): void;

    /**
     * Dispatch a job after a delay.
     *
     * @param  object                   $job
     * @param  DateTimeInterface|int    $delay      Delay in seconds or an absolute time.
     * @param  QueuePriority            $priority
     * @return void
     */
    public function later(
        object                $job,
        DateTimeInterface|int $delay,
        QueuePriority         $priority = QueuePriority::Default,
    ): void;

    /**
     * Dispatch a job synchronously, bypassing the queue.
     * Use only where immediate execution is explicitly required.
     *
     * @param  object $job
     * @return void
     */
    public function dispatchSync(object $job): void;
}
