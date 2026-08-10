<?php
declare(strict_types=1);
use App\Domains\Subscription\Enums\SubscriptionStatus;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\SubscriptionEntitlementService;
use App\Domains\Subscription\Services\SubscriptionTransitionService;
use App\Domains\Subscription\Enums\SubscriptionTrigger;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->entitlement = new SubscriptionEntitlementService();
    $this->sub = Subscription::create([
        'id' => Subscription::newUuid(), 'organization_id' => 'org-active',
        'plan_code' => 'starter', 'status' => SubscriptionStatus::Active,
        'trial_starts_at' => now(), 'trial_ends_at' => now()->addDays(30),
    ]);
});

test('active organization has full access', function () {
    expect($this->entitlement->isAccessRestricted('org-active'))->toBeFalse();
    expect($this->entitlement->getAccessLevel('org-active'))->toBe('full');
});

test('expired organization has restricted access', function () {
    $this->sub->update(['status' => SubscriptionStatus::Expired]);
    expect($this->entitlement->isAccessRestricted('org-active'))->toBeTrue();
    expect($this->entitlement->getAccessLevel('org-active'))->toBe('restricted');
});

test('trial organization has full access', function () {
    $this->sub->update(['status' => SubscriptionStatus::Trial]);
    expect($this->entitlement->isAccessRestricted('org-active'))->toBeFalse();
});

test('past_due organization has full access', function () {
    $this->sub->update(['status' => SubscriptionStatus::PastDue]);
    expect($this->entitlement->isAccessRestricted('org-active'))->toBeFalse();
});

test('grace organization has full access', function () {
    $this->sub->update(['status' => SubscriptionStatus::Grace]);
    expect($this->entitlement->isAccessRestricted('org-active'))->toBeFalse();
});

test('cancelled organization has restricted access', function () {
    $this->sub->update(['status' => SubscriptionStatus::Cancelled]);
    expect($this->entitlement->isAccessRestricted('org-active'))->toBeTrue();
});

test('nonexistent organization is not restricted', function () {
    expect($this->entitlement->isAccessRestricted('nonexistent'))->toBeFalse();
});

test('always allowed paths for expired organizations', function () {
    expect($this->entitlement->isAlwaysAllowed('subscription'))->toBeTrue();
    expect($this->entitlement->isAlwaysAllowed('billing/invoices'))->toBeTrue();
    expect($this->entitlement->isAlwaysAllowed('payment/checkout'))->toBeTrue();
    expect($this->entitlement->isAlwaysAllowed('clinic/settings'))->toBeTrue();
    expect($this->entitlement->isAlwaysAllowed('logout'))->toBeTrue();
    expect($this->entitlement->isAlwaysAllowed('health'))->toBeTrue();
});

test('operational paths are not always allowed', function () {
    expect($this->entitlement->isAlwaysAllowed('patients'))->toBeFalse();
    expect($this->entitlement->isAlwaysAllowed('emr'))->toBeFalse();
    expect($this->entitlement->isAlwaysAllowed('odontogram'))->toBeFalse();
    expect($this->entitlement->isAlwaysAllowed('treatment'))->toBeFalse();
    expect($this->entitlement->isAlwaysAllowed('inventory'))->toBeFalse();
});

test('subscription restricted states are correct', function () {
    $restricted = SubscriptionStatus::restrictedStates();
    expect($restricted)->toContain(SubscriptionStatus::Expired, SubscriptionStatus::Cancelled);
    expect($restricted)->not->toContain(SubscriptionStatus::Active, SubscriptionStatus::Trial, SubscriptionStatus::Grace, SubscriptionStatus::PastDue);
});
