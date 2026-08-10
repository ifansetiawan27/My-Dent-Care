<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Http\Middleware;
use App\Domains\Subscription\Services\SubscriptionEntitlementService;
use Closure;
use Illuminate\Http\Request;

final class SubscriptionAccessMiddleware
{
    public function __construct(private SubscriptionEntitlementService $entitlement) {}

    public function handle(Request $request, Closure $next) {
        $user = $request->user();
        if (!$user) return $next($request);
        $orgId = $user->organization_id;
        if (!$orgId) return $next($request);
        $path = $request->path();
        if ($this->entitlement->isAlwaysAllowed($path)) return $next($request);
        if ($this->entitlement->isAccessRestricted($orgId)) {
            return response()->json([
                'success' => false, 'message' => 'Subscription has expired. Please reactivate to continue.',
                'code' => 'SUBSCRIPTION_EXPIRED', 'action' => 'REACTIVATE',
            ], 403);
        }
        return $next($request);
    }
}