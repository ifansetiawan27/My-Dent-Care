<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Enums;
enum ScannerDeviceStatus: string {
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
    public function label(): string { return match($this) { self::Active => 'Active', self::Maintenance => 'Maintenance', self::Retired => 'Retired' }; }
    public static function values(): array { return array_column(self::cases(), 'value'); }
}
