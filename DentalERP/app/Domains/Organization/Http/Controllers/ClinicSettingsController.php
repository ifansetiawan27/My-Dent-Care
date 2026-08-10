<?php
declare(strict_types=1);
namespace App\Domains\Organization\Http\Controllers;
use App\Domains\Organization\Models\Organization;
use App\Domains\Subscription\Http\Resources\SubscriptionResource;
use App\Domains\Subscription\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ClinicSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $org = Organization::findOrFail(auth()->user()->organization_id);
        $sub = Subscription::where('organization_id', $org->id)->first();
        return response()->json([
            'clinic' => [
                'name' => $org->company_name, 'legal_name' => $org->legal_name,
                'email' => $org->email, 'phone' => $org->phone, 'website' => $org->website,
                'address' => $org->address, 'city' => $org->city, 'province' => $org->province,
                'country' => $org->country, 'postal_code' => $org->postal_code,
                'logo' => $org->logo,
            ],
            'invoice' => [
                'prefix' => $org->invoice_prefix, 'footer' => $org->invoice_footer,
            ],
            'billing' => [
                'name' => $org->billing_name, 'email' => $org->billing_email,
                'phone' => $org->billing_phone, 'address' => $org->billing_address,
            ],
            'subscription' => $sub ? (new SubscriptionResource($sub))->resolve() : null,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $org = Organization::findOrFail(auth()->user()->organization_id);
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:200', 'legal_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:100', 'phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:200',
            'address' => 'nullable|string', 'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100', 'postal_code' => 'nullable|string|max:10',
            'invoice_prefix' => 'nullable|string|max:10', 'invoice_footer' => 'nullable|string|max:500',
            'billing_name' => 'nullable|string|max:200', 'billing_email' => 'nullable|email|max:100',
            'billing_phone' => 'nullable|string|max:20', 'billing_address' => 'nullable|string',
        ]);
        $org->update($validated);
        return $this->show();
    }
}