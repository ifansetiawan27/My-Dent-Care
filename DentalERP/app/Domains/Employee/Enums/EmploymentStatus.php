<?php
declare(strict_types=1);
namespace App\Domains\Employee\Enums;
enum EmploymentStatus: string {
    case Permanent = 'permanent'; case Contract = 'contract'; case Probation = 'probation'; case Terminated = 'terminated';
    public function label(): string { return match($this) { self::Permanent => 'Permanent', self::Contract => 'Contract', self::Probation => 'Probation', self::Terminated => 'Terminated' }; }
    public static function values(): array { return array_column(self::cases(), 'value'); }
}
