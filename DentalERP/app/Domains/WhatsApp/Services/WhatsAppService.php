<?php

declare(strict_types=1);

namespace App\Domains\WhatsApp\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WhatsAppService - manages WhatsApp connection via a bridge gateway.
 *
 * This service communicates with a Node.js WhatsApp bridge (baileys-based)
 * running on a configurable port. The bridge handles QR code generation
 * and message sending.
 *
 * For production, replace with an official WhatsApp Business API provider.
 */
class WhatsAppService
{
    private string $bridgeUrl;
    private int $timeout;

    public function __construct()
    {
        $this->bridgeUrl = rtrim(config('whatsapp.bridge_url', 'http://localhost:3000'), '/');
        $this->timeout = config('whatsapp.timeout', 30);
    }

    /**
     * Get current WhatsApp session status.
     */
    public function getSessionStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->bridgeUrl}/api/status");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('WhatsApp bridge unreachable: ' . $e->getMessage());
        }

        return ['status' => 'disconnected', 'message' => 'Bridge not reachable'];
    }

    /**
     * Generate QR code for WhatsApp login.
     * Returns base64 encoded QR image.
     */
    public function generateQR(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->bridgeUrl}/api/qr");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate WhatsApp QR: ' . $e->getMessage());
        }

        return ['status' => 'error', 'message' => 'Failed to generate QR'];
    }

    /**
     * Send a WhatsApp message to a phone number.
     *
     * @param string $phone  Phone number with country code (e.g., 6281234567890)
     * @param string $message  Message text
     */
    public function sendMessage(string $phone, string $message): array
    {
        // Normalize phone number: remove +, spaces, dashes
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ensure country code
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->bridgeUrl}/api/send", [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error',
                'message' => $response->json()['message'] ?? 'Failed to send message',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp message: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Bridge not reachable'];
        }
    }

    /**
     * Send appointment reminder message.
     */
    public function sendAppointmentReminder(array $appointment): array
    {
        $patientName = $appointment['patient']['full_name'] ?? 'Pasien';
        $doctorName = $appointment['doctor']['full_name'] ?? 'Dokter';
        $scheduledAt = $appointment['scheduled_at'] ?? '';
        $type = $appointment['type'] ?? 'appointment';

        $formattedDate = $scheduledAt
            ? now()->parse($scheduledAt)->locale('id')->translatedFormat('l, d F Y')
            : '';
        $formattedTime = $scheduledAt
            ? now()->parse($scheduledAt)->format('H:i') . ' WIB'
            : '';

        $typeLabels = [
            'checkup' => 'Check-up',
            'treatment' => 'Perawatan',
            'consultation' => 'Konsultasi',
            'follow_up' => 'Follow-up',
            'emergency' => 'Darurat',
        ];
        $typeLabel = $typeLabels[$type] ?? $type;

        $message = "🦾 *My Dent Care - Appointment Reminder*

Halo, {$patientName}! 👋

Ini adalah pengingat untuk janji temu Anda:

📅 *{$formattedDate}*
⏰ *{$formattedTime}*
👨‍⚕️ *{$doctorName}*
📋 *{$typeLabel}*

Mohon hadir 15 menit sebelum jadwal.

Jika ingin membatalkan atau menjadwal ulang, silakan hubungi klinik.

_Terima kasih, sampai jumpa!_ 😊";

        $phone = $appointment['patient']['phone'] ?? '';
        if (!$phone) {
            return ['status' => 'error', 'message' => 'Patient phone number not found'];
        }

        return $this->sendMessage($phone, $message);
    }

    /**
     * Disconnect WhatsApp session.
     */
    public function disconnect(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->bridgeUrl}/api/logout");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to disconnect WhatsApp: ' . $e->getMessage());
        }

        return ['status' => 'error', 'message' => 'Failed to disconnect'];
    }
}
