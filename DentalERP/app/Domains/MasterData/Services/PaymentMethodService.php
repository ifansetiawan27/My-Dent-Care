<?php

declare(strict_types=1);

namespace App\Domains\MasterData\Services;

use App\Domains\MasterData\Repositories\PaymentMethodRepository;

/**
 * PaymentMethodService
 *
 * Business operations for the payment_methods reference table.
 */
class PaymentMethodService extends BaseMasterDataService
{
    public function __construct(PaymentMethodRepository $repository)
    {
        parent::__construct($repository);
        $this->serviceName = 'PaymentMethodService';
    }
}
