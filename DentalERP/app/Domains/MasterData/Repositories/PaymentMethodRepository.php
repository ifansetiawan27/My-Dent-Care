<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Repositories;

use App\Domains\MasterData\Models\PaymentMethod;

/**
 * PaymentMethodRepository
 *
 * Data access for the payment_methods reference table.
 */
class PaymentMethodRepository extends BaseMasterDataRepository
{
    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }
}
