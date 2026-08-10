<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Exceptions;
use App\Core\Exceptions\BusinessException;
final class SubscriptionExpiredException extends BusinessException {
    public function __construct(string $organizationId) {
        parent::__construct("Subscription has expired for organization [{$organizationId}]. Reactivate to continue.");
    }
}