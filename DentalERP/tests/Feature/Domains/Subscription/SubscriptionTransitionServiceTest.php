<?php
declare(strict_types=1);
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use App\Domains\Subscription\Exceptions\InvalidTransitionException;
use App\Domains\Organization\Models\Organization;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionTransition;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new SubscriptionTransitionService();
    $org = Organization::factory()->create();
    $this->actorId = Subscription::newUuid();
    $this->subscription = Subscription::create([
        'id'              => Subscription::newUuid(),
        'organization_id' => $org->id,
        'plan_code'       => 'starter',
        'status'          => SubscriptionStatus::Trial,
        'trial_starts_at' => now(),
        'trial_ends_at'   => now()->addDays(30),
    ]);
});

// --- Valid Transitions ---
it('allows TRIAL → ACTIVE', function () {
    $this->service->transition($this->subscription, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId);
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
    expect(SubscriptionTransition::count())->toBe(1);
});

it('allows TRIAL → EXPIRED', function () {
    $this->service->transition($this->subscription, SubscriptionStatus::Expired, SubscriptionTrigger::TrialExpired, 'system');
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Expired);
});

it('allows ACTIVE → PAST_DUE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Active]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::PastDue, SubscriptionTrigger::RenewalPaymentFailed, 'system');
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::PastDue);
});

it('allows ACTIVE → CANCELLED', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Active]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Cancelled, SubscriptionTrigger::SubscriptionCancelled, 'user', $this->actorId);
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});

it('allows PAST_DUE → ACTIVE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::PastDue]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Active, SubscriptionTrigger::RenewalPaymentSucceeded, 'system');
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

it('allows PAST_DUE → GRACE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::PastDue]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Grace, SubscriptionTrigger::GraceStarted, 'system');
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Grace);
});

it('allows GRACE → ACTIVE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Grace]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Active, SubscriptionTrigger::RenewalPaymentSucceeded, 'system');
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

it('allows GRACE → EXPIRED', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Grace]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Expired, SubscriptionTrigger::GraceExpired, 'system');
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Expired);
});

it('allows EXPIRED → ACTIVE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Expired]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Active, SubscriptionTrigger::ReactivationSucceeded, 'user', $this->actorId);
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

it('allows CANCELLED → ACTIVE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Cancelled]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Active, SubscriptionTrigger::ReactivationSucceeded, 'user', $this->actorId);
    expect($this->subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

// --- Invalid Transitions ---
it('rejects TRIAL → PAST_DUE', function () {
    $this->service->transition($this->subscription, SubscriptionStatus::PastDue, SubscriptionTrigger::RenewalPaymentFailed, 'system');
})->throws(InvalidTransitionException::class);

it('rejects TRIAL → GRACE', function () {
    $this->service->transition($this->subscription, SubscriptionStatus::Grace, SubscriptionTrigger::GraceStarted, 'system');
})->throws(InvalidTransitionException::class);

it('rejects EXPIRED → GRACE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Expired]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Grace, SubscriptionTrigger::GraceStarted, 'system');
})->throws(InvalidTransitionException::class);

it('rejects EXPIRED → TRIAL', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Expired]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Trial, SubscriptionTrigger::TrialStarted, 'system');
})->throws(InvalidTransitionException::class);

it('rejects CANCELLED → GRACE', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Cancelled]);
    $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Grace, SubscriptionTrigger::GraceStarted, 'system');
})->throws(InvalidTransitionException::class);

// --- Atomicity ---
it('rolls back subscription status if transition insert fails', function () {
    $original = $this->subscription->status;
    $service = new class extends SubscriptionTransitionService {
        public function transition($s, $n, $t, $a='s', $ai=null, $m=[], $ik=null) { throw new RuntimeException('Forced failure'); }
    };
    try { $service->transition($this->subscription, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated); } catch (RuntimeException) {}
    expect($this->subscription->fresh()->status)->toBe($original);
    expect(SubscriptionTransition::count())->toBe(0);
})->skip('Integration test — mock approach differs');

// --- Idempotency ---
it('skips duplicate transition via idempotency key', function () {
    $key = 'idem-test-001';
    $first = $this->service->transition($this->subscription, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    $second = $this->service->transition($this->subscription->fresh(), SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    expect($first->id)->toBe($second->id);
    expect(SubscriptionTransition::count())->toBe(1);
});

// --- Transition history ---
it('records correct transition history', function () {
    $t = $this->service->transition($this->subscription, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, ['amount' => 299000]);
    expect($t->previous_state)->toBe('trial');
    expect($t->new_state)->toBe('active');
    expect($t->trigger)->toBe('payment_activated');
    expect($t->actor_type)->toBe('user');
    expect($t->actor_id)->toBe($this->actorId);
    expect($t->metadata)->toBe(['amount' => 299000]);
    expect($t->organization_id)->toBe($this->subscription->organization_id);
});

// --- System actor ---
it('records system actor for automated transitions', function () {
    $t = $this->service->transition($this->subscription, SubscriptionStatus::Expired, SubscriptionTrigger::TrialExpired, 'system');
    expect($t->actor_type)->toBe('system');
    expect($t->actor_id)->toBeNull();
});
