<?php

declare(strict_types=1);

namespace App\Domains\Authentication\Interfaces;

use App\Domains\Authentication\DTO\TokenPairDTO;

interface TokenServiceInterface
{
    public function refresh(string $refreshToken): TokenPairDTO;
}
