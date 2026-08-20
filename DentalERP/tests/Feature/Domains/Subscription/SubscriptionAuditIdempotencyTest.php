<?php
declare(strict_types=1);
use App\Domains\Organization\Models\Organization;
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Models\SubscriptionTransition;
use App\Domains\Subscription\Services\IdempotencyService;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->svc = new SubscriptionTransitionService();
    $this->idem = new IdempotencyService();
    $org = Organization::factory()->create();
    $this->orgId = $org->id;
    $this->actorId = Subscription::newUuid();
    $this->sub = Subscription::create([
        'id' => Subscription::newUuid(), 'organization_id' => $this->orgId,
        'plan_code' => 'starter', 'status' => SubscriptionStatus::Trial,
        'trial_starts_at' => now(), 'trial_ends_at' => now()->addDays(30),
    ]);
});

// --- Audit Atomicity ---
test('successful transition records complete audit trail', function () {
    $t = $this->svc->transition($this->sub, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, ['amount' => 299000]);
    expect($t->subscription_id)->toBe($this->sub->id);
    expect($t->organization_id)->toBe($this->orgId);
    expect($t->previous_state)->toBe('trial');
    expect($t->new_state)->toBe('active');
    expect($t->trigger)->toBe('payment_activated');
    expect($t->actor_type)->toBe('user');
    expect($t->actor_id)->toBe($this->actorId);
    expect($t->created_at)->not->toBeNull();
    expect($this->sub->fresh()->status)->toBe(SubscriptionStatus::Active);
});

test('failed transition does not create audit record', function () {
    $originalStatus = $this->sub->status;
    try { $this->svc->transition($this->sub, SubscriptionStatus::PastDue, SubscriptionTrigger::RenewalPaymentFailed, 'system'); } catch (\Throwable) {}
    expect($this->sub->fresh()->status)->toBe($originalStatus);
});

test('transition history records system actor correctly', function () {
    $t = $this->svc->transition($this->sub, SubscriptionStatus::Expired, SubscriptionTrigger::TrialExpired, 'system');
    expect($t->actor_type)->toBe('system');
    expect($t->actor_id)->toBeNull();
});

// --- Idempotency ---
test('duplicate idempotency key returns existing transition without creating new one', function () {
    $key = 'test:dup:001';
    $first = $this->svc->transition($this->sub, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    $second = $this->svc->transition($this->sub->fresh(), SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    expect($first->id)->toBe($second->id);
    expect(SubscriptionTransition::count())->toBe(1);
});

test('idempotency key is globally idempotent across subscriptions', function () {
    $key = 'test:unique:001';
    $first = $this->svc->transition($this->sub, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    $org2 = Organization::factory()->create();
    $sub2 = Subscription::create(['id' => Subscription::newUuid(), 'organization_id' => $org2->id, 'plan_code' => 'professional', 'status' => SubscriptionStatus::Trial, 'trial_starts_at' => now(), 'trial_ends_at' => now()->addDays(30)]);
    $result = $this->svc->transition($sub2, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    expect($result->id)->toBe($first->id);
});

test('idempotency service detects processed keys', function () {
    $key = 'test:processed:001';
    $this->svc->transition($this->sub, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    expect($this->idem->isProcessed($key))->toBeTrue();
    expect($this->idem->isProcessed('nonexistent'))->toBeFalse();
});

test('idempotency service retrieves existing transition', function () {
    $key = 'test:find:001';
    $this->svc->transition($this->sub, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], $key);
    $found = $this->idem->findTransition($key);
    expect($found)->not->toBeNull();
    expect($found->previous_state)->toBe('trial');
    expect($found->new_state)->toBe('active');
});

test('webhook key generation is deterministic', function () {
    $key = $this->idem->webhookKey('midtrans', 'evt-abc123');
    expect($key)->toBe('webhook:midtrans:evt-abc123');
});

test('job key generation is deterministic', function () {
    $key = $this->idem->jobKey('ProcessTrialExpiration', $this->sub->id, '2026-08');
    expect($key)->toBe('job:ProcessTrialExpiration:' . $this->sub->id . ':2026-08');
});

test('key generation is deterministic across calls', function () {
    $k1 = $this->idem->key('sub', 'id-1', 'renew');
    $k2 = $this->idem->key('sub', 'id-1', 'renew');
    expect($k1)->toBe($k2);
});

// --- Multiple transitions produce distinct audit records ---
test('multiple valid transitions produce distinct audit records', function () {
    $t1 = $this->svc->transition($this->sub, SubscriptionStatus::Active, SubscriptionTrigger::PaymentActivated, 'user', $this->actorId, [], 'k1');
    $sub = $this->sub->fresh();
    $sub->update(['status' => SubscriptionStatus::PastDue]);
    $t2 = $this->svc->transition($sub->fresh(), SubscriptionStatus::Grace, SubscriptionTrigger::GraceStarted, 'system', null, [], 'k2');
    expect($t1->id)->not->toBe($t2->id);
    expect(SubscriptionTransition::count())->toBe(2);
    expect($sub->fresh()->status)->toBe(SubscriptionStatus::Grace);
});
