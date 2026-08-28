<?php

declare(strict_types=1);

namespace App\Domains\Appointment\Services\ReminderService;

use App\Domains\Notification\Services\NotificationQueueService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AppointmentReminderService - checks upcoming appointments and queues reminders.
 */
class AppointmentReminderService
{
    public function __construct(
        private readonly NotificationQueueService $notificationQueue,
    ) {}

    /**
     * Check for appointments that need reminders and queue them.
     *
     * This should be called by the scheduler every 5-10 minutes.
     * It finds appointments where:
     *   - reminder_minutes is set
     *   - reminder_sent is false
     *   - scheduled_at is within reminder_minutes from now
     *   - status is scheduled or confirmed (not cancelled)
     */
    public function processReminders(): array
    {
        $now = now();
        $reminderWindow = now()->copy()->addMinutes(15); // look ahead 15 min window

        $appointments = DB::table('appointments')
            ->whereNotNull('reminder_minutes')
            ->where('reminder_sent', false)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('scheduled_at', '>=', $now)
            ->where('scheduled_at', '<=', $reminderWindow)
            ->get();

        $queued = 0;
        $skipped = 0;

        foreach ($appointments as $appointment) {
            // Check if it's time to send the reminder
            $scheduledAt = \Carbon\Carbon::parse($appointment->scheduled_at);
            $reminderTime = $scheduledAt->copy()->subMinutes($appointment->reminder_minutes);

            if ($now->gte($reminderTime) && $now->lt($reminderTime->copy()->addMinutes(15))) {
                // Queue the reminder
                $patient = DB::table('patients')->find($appointment->patient_id);
                $doctor = DB::table('doctors')->find($appointment->doctor_id);

                if (!$patient || !$patient->phone) {
                    $skipped++;
                    Log::warning("Skipping reminder for appointment {$appointment->id}: no patient phone");
                    continue;
                }

                $this->notificationQueue->queue([
                    'channel' => 'whatsapp',
                    'recipient' => preg_replace('/[^0-9]/', '', $patient->phone),
                    'template' => 'appointment_reminder',
                    'payload' => [
                        'patient' => [
                            'full_name' => $patient->full_name,
                            'phone' => $patient->phone,
                        ],
                        'doctor' => [
                            'full_name' => $doctor?->full_name ?? 'Dokter',
                        ],
                        'scheduled_at' => $appointment->scheduled_at,
                        'type' => $appointment->type,
                    ],
                    'reference_id' => $appointment->id,
                    'scheduled_at' => now(),
                ]);

                // Mark reminder as sent
                DB::table('appointments')
                    ->where('id', $appointment->id)
                    ->update(['reminder_sent' => true, 'updated_at' => now()]);

                $queued++;
                Log::info("Queued WhatsApp reminder for appointment {$appointment->id} at {$appointment->scheduled_at}");
            } else {
                $skipped++;
            }
        }

        return ['queued' => $queued, 'skipped' => $skipped];
    }
}
