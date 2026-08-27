<?php

declare(strict_types=1);

namespace App\Domains\Finance\Enums;

enum ReportType: string
{
    case BALANCE_SHEET = 'balance_sheet';
    case INCOME_STATEMENT = 'income_statement';
    case CASH_FLOW = 'cash_flow';
    case TRIAL_BALANCE = 'trial_balance';

    public function label(): string
    {
        return match ($this) {
            self::BALANCE_SHEET => 'Balance Sheet',
            self::INCOME_STATEMENT => 'Income Statement',
            self::CASH_FLOW => 'Cash Flow Statement',
            self::TRIAL_BALANCE => 'Trial Balance',
        };
    }
}
