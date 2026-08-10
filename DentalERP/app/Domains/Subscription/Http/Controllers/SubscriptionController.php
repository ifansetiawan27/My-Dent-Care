<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Http\Controllers;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use App\Domains\Subscription\Http\Resources\SubscriptionResource;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionTransitionService $ts) {}

    public function show(): JsonResponse
    {
        $sub = Subscription::where('organization_id', auth()->user()->organization_id)->first();
        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'No subscription found.'], 404);
        }
        return (new SubscriptionResource($sub))->response();
    }

    public function cancel(): JsonResponse
    {
        $sub = Subscription::where('organization_id', auth()->user()->organization_id)->firstOrFail();
        $this->ts->transition($sub, SubscriptionStatus::Cancelled, SubscriptionTrigger::SubscriptionCancelled, 'user', auth()->id());
        return (new SubscriptionResource($sub->fresh()))->response();
    }

    public function plans(): JsonResponse
    {
        return response()->json(['data' => [
            ['code' => 'starter',      'name' => 'Starter',      'price' => 299000, 'storage_gb' => 10,  'branches' => 1],
            ['code' => 'professional',  'name' => 'Professional',  'price' => 399000, 'storage_gb' => 50,  'branches' => 5],
            ['code' => 'enterprise',   'name' => 'Enterprise',   'price' => 499000, 'storage_gb' => 500, 'branches' => -1],
        ]]);
    }
}