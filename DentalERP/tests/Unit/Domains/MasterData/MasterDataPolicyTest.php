<?php

declare(strict_types=1);

use App\Domains\MasterData\Policies\MasterDataPolicy;
use App\Domains\MasterData\Models\Country;
use App\Domains\User\Models\User;

it('allows viewAny for any authenticated user', function (): void {
    $user = mock(User::class);
    $policy = new MasterDataPolicy();
    expect($policy->viewAny($user))->toBeTrue();
});

it('allows view for any authenticated user', function (): void {
    $user = mock(User::class);
    $policy = new MasterDataPolicy();
    expect($policy->view($user, new Country()))->toBeTrue();
});

it('restricts create to Super Admin and Owner', function (): void {
    $admin = mock(User::class);
    $admin->shouldReceive('hasRole')->with(['Super Admin', 'Owner'])->andReturn(true);
    $policy = new MasterDataPolicy();
    expect($policy->create($admin))->toBeTrue();
});

it('denies create for non-admin user', function (): void {
    $user = mock(User::class);
    $user->shouldReceive('hasRole')->with(['Super Admin', 'Owner'])->andReturn(false);
    $policy = new MasterDataPolicy();
    expect($policy->create($user))->toBeFalse();
});

it('restricts update to Super Admin and Owner', function (): void {
    $admin = mock(User::class);
    $admin->shouldReceive('hasRole')->with(['Super Admin', 'Owner'])->andReturn(true);
    $policy = new MasterDataPolicy();
    expect($policy->update($admin, new Country()))->toBeTrue();
});

it('restricts delete to Super Admin and Owner', function (): void {
    $admin = mock(User::class);
    $admin->shouldReceive('hasRole')->with(['Super Admin', 'Owner'])->andReturn(true);
    $policy = new MasterDataPolicy();
    expect($policy->delete($admin, new Country()))->toBeTrue();
});
