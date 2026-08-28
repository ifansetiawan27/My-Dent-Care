<?php

declare(strict_types=1);

namespace App\Domains\WhatsApp\Controllers;

use App\Domains\WhatsApp\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class WhatsAppController extends Controller
{
    public function __construct(
        private readonly WhatsAppService $waService,
    ) {}

    /**
     * Get WhatsApp connection status.
     */
    public function status(): JsonResponse
    {
        return response()->json($this->waService->getSessionStatus());
    }

    /**
     * Generate QR code for login.
     */
    public function generateQR(): JsonResponse
    {
        return response()->json($this->waService->generateQR());
    }

    /**
     * Disconnect WhatsApp session.
     */
    public function disconnect(): JsonResponse
    {
        return response()->json($this->waService->disconnect());
    }

    /**
     * Test send WhatsApp message.
     */
    public function testSend(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->waService->sendMessage(
            $request->input('phone'),
            $request->input('message'),
        );

        return response()->json($result);
    }

    /**
     * Send a test appointment reminder.
     */
    public function testReminder(): JsonResponse
    {
        $sampleAppointment = [
            'patient' => [
                'full_name' => 'Test Patient',
                'phone' => '+6281234567890',
            ],
            'doctor' => [
                'full_name' => 'dr. Test Doctor',
            ],
            'scheduled_at' => now()->addHours(2)->toDateTimeString(),
            'type' => 'checkup',
        ];

        $result = $this->waService->sendAppointmentReminder($sampleAppointment);

        return response()->json($result);
    }
}
