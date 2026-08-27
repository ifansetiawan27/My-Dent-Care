<?php

declare(strict_types=1);

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domains\Finance\Models\JournalEntry */
final class JournalEntryResource extends JsonResource
{
    public function toArray(Request $r): array
    {
        return [
            'id'             => $this->id,
            'entry_number'   => $this->entry_number,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'entry_date'     => $this->entry_date?->toDateString(),
            'period_date'    => $this->period_date?->toDateString(),
            'description'    => $this->description,
            'status'         => $this->status,
            'total_debit'    => $this->total_debit,
            'total_credit'   => $this->total_credit,
            'is_balanced'    => $this->is_balanced,
            'posted_by'      => $this->posted_by,
            'posted_at'      => $this->posted_at?->toISOString(),
            'lines'          => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line) => [
                'id'          => $line->id,
                'account_id'  => $line->account_id,
                'entry_type'  => $line->entry_type,
                'amount'      => $line->amount,
                'description' => $line->description,
                'account'     => $line->whenLoaded('account', fn () => [
                    'id'           => $line->account->id,
                    'account_code' => $line->account->account_code,
                    'account_name' => $line->account->account_name,
                ]),
            ])),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
