<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Models;

/**
 * PaymentMethod
 *
 * Payment method reference (Cash, Transfer, Card, E-wallet, Insurance).
 *
 * @property string      $type
 * @property string|null $description
 */
class PaymentMethod extends BaseMasterDataModel
{
    protected $table = 'payment_methods';

    /**
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
