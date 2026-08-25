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
        $user = auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }
        $sub = Subscription::where('organization_id', $user->organization_id)->first();
        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'No subscription found.'], 404);
        }
        return (new SubscriptionResource($sub))->response();
    }

    public function cancel(): JsonResponse
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }
        $sub = Subscription::where('organization_id', $user->organization_id)->firstOrFail();
        $this->ts->transition($sub, SubscriptionStatus::Cancelled, SubscriptionTrigger::SubscriptionCancelled, 'user', auth()->id());
        $sub->refresh();
        return (new SubscriptionResource($sub))->response();
    }

    public function plans(): JsonResponse
    {
        // Single plan — konsisten dengan landing page
        return response()->json(['data' => [
            ['code' => 'professional', 'name' => 'My Dent Care', 'price' => 299000, 'storage_gb' => -1, 'branches' => 1, 'trial_days' => 30],
        ]]);
    }
}