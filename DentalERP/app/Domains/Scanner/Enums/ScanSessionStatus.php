<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Enums;
enum ScanSessionStatus: string {
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Failed = 'failed';
    public function label(): string { return match($this) { self::InProgress => 'In Progress', self::Completed => 'Completed', self::Failed => 'Failed' }; }
    public function isTerminal(): bool { return match($this) { self::Completed, self::Failed => true, default => false }; }
    public static function values(): array { return array_column(self::cases(), 'value'); }
}
