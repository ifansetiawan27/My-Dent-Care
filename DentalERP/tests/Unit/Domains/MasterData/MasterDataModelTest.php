<?php

declare(strict_types=1);

use App\Domains\MasterData\Models\Country;
use App\Domains\MasterData\Models\Nationality;
use App\Domains\MasterData\Models\BaseMasterDataModel;

it('Country model extends BaseMasterDataModel', function (): void {
    $model = new Country();
    expect($model)->toBeInstanceOf(BaseMasterDataModel::class);
    expect($model->getTable())->toBe('countries');
});

it('BaseMasterDataModel inherits HasUuid trait', function (): void {
    $traits = class_uses_recursive(BaseMasterDataModel::class);
    expect($traits)->toHaveKey('App\Core\Traits\HasUuid');
});

it('BaseMasterDataModel inherits HasAudit trait', function (): void {
    $traits = class_uses_recursive(BaseMasterDataModel::class);
    expect($traits)->toHaveKey('App\Core\Traits\HasAudit');
});

it('BaseMasterDataModel inherits SoftDeletes', function (): void {
    $traits = class_uses_recursive(BaseMasterDataModel::class);
    expect($traits)->toHaveKey('Illuminate\Database\Eloquent\SoftDeletes');
});

it('fillable whitelist excludes system fields', function (): void {
    $model = new Country();
    $fillable = $model->getFillable();
    expect($fillable)->toContain('code', 'name', 'is_active');
    expect($fillable)->not->toContain('id', 'created_at', 'updated_at', 'deleted_at');
});

it('casts is_active as boolean', function (): void {
    $model = new Country();
    expect($model->getCasts()['is_active'])->toBe('boolean');
});

it('hidden contains deleted_at and deleted_by', function (): void {
    $model = new Country();
    expect($model->getHidden())->toContain('deleted_at', 'deleted_by');
});

it('6 new models all extend BaseMasterDataModel', function (): void {
    $models = [Nationality::class, \App\Domains\MasterData\Models\TreatmentCategory::class,
        \App\Domains\MasterData\Models\AppointmentStatus::class, \App\Domains\MasterData\Models\LaboratoryCategory::class,
        \App\Domains\MasterData\Models\AssetCategory::class, \App\Domains\MasterData\Models\InventoryCategory::class,
    ];
    foreach ($models as $class) {
        expect(new $class())->toBeInstanceOf(BaseMasterDataModel::class);
    }
});

it('Country model has correct table mapping', function (): void {
    expect((new Country())->getTable())->toBe('countries');
});

it('is_active defaults to true', function (): void {
    $model = new Country();
    expect($model->is_active)->toBeNull();
});
