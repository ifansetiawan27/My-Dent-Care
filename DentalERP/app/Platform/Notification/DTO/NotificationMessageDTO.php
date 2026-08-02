<?php

declare(strict_types=1);

namespace App\Platform\Notification\DTO;

use App\Platform\Notification\Enums\NotificationChannel;

/**
 * NotificationMessageDTO
 *
 * Immutable value object describing a notification to be dispatched.
 * Domains construct this and pass it to NotificationServiceInterface::send().
 * The Notification Platform fans it out to each channel via Queue.
 */
final readonly class NotificationMessageDTO
{
    /**
     * @param  string                        $type            Notification type (e.g. 'appointment_reminder').
     * @param  string                        $notifiableType  Recipient model class (User, Patient).
     * @param  string                        $notifiableId    Recipient UUID.
     * @param  array<int, NotificationChannel> $channels      Target delivery channels.
     * @param  string                        $title           Notification title.
     * @param  string                        $body            Notification body.
     * @param  string|null                   $organizationId  Tenant organization UUID.
     * @param  string|null                   $branchId        Tenant branch UUID.
     * @param  array<string, mixed>          $data            Extra payload (deep link, metadata).
     * @param  string|null                   $locale          Preferred language (default id).
     */
    public function __construct(
        public string  $type,
        public string  $notifiableType,
        public string  $notifiableId,
        public array   $channels,
        public string  $title,
        public string  $body,
        public ?string $organizationId = null,
        public ?string $branchId       = null,
        public array   $data           = [],
        public ?string $locale         = 'id',
    ) {}

    /**
     * Get channel values as plain strings.
     *
     * @return array<string>
     */
    public function channelValues(): array
    {
        return array_map(
            static fn (NotificationChannel $c): string => $c->value,
            $this->channels,
        );
    }

    /**
     * Serialize to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type'            => $this->type,
            'notifiable_type' => $this->notifiableType,
            'notifiable_id'   => $this->notifiableId,
            'channels'        => $this->channelValues(),
            'title'           => $this->title,
            'body'            => $this->body,
            'organization_id' => $this->organizationId,
            'branch_id'       => $this->branchId,
            'data'            => $this->data,
            'locale'          => $this->locale,
        ];
    }
}
