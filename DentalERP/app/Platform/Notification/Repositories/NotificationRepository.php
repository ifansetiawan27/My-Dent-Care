<?php

declare(strict_types=1);

namespace App\Platform\Notification\Repositories;

use App\Platform\Notification\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository
{
    public function __construct(
        private readonly Notification $model
    ) {
    }

    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }

    public function findById(string $id): ?Notification
    {
        return $this->model->find($id);
    }

    public function findByRecipient(string $notifiableType, string $notifiableId, int $limit = 50): Collection
    {
        return $this->model
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function findUnreadByRecipient(string $notifiableType, string $notifiableId): Collection
    {
        return $this->model
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPendingNotifications(int $limit = 100): Collection
    {
        return $this->model
            ->where('status', 'pending')
            ->where('retry_count', '<', 3)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function findFailedNotifications(int $limit = 100): Collection
    {
        return $this->model
            ->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->orderBy('failed_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function update(string $id, array $data): bool
    {
        $notification = $this->findById($id);
        
        if (!$notification) {
            return false;
        }

        return $notification->update($data);
    }

    public function markAsRead(string $id): bool
    {
        $notification = $this->findById($id);

        if (!$notification || $notification->channel !== 'in_app') {
            return false;
        }

        return $notification->update([
            'read_at' => now(),
            'status'  => 'read',
        ]);
    }

    public function markAsSent(string $id, ?string $externalId = null): bool
    {
        return $this->update($id, [
            'status' => 'sent',
            'sent_at' => now(),
            'external_id' => $externalId,
        ]);
    }

    public function markAsFailed(string $id, string $errorMessage): bool
    {
        $notification = $this->findById($id);
        
        if (!$notification) {
            return false;
        }

        return $notification->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failed_reason' => $errorMessage,
            'retry_count' => $notification->retry_count + 1,
        ]);
    }

    public function delete(string $id): bool
    {
        $notification = $this->findById($id);
        
        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }
}
