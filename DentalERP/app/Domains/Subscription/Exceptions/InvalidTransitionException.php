<?php
declare(strict_types=1);
namespace App\Domains\Subscription\Exceptions;
use App\Core\Exceptions\BusinessException;
final class InvalidTransitionException extends BusinessException {
    public function __construct(string $from, string $to) {
        parent::__construct("Invalid subscription transition from '{$from}' to '{$to}'.");
    }
}