<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Enums;
enum ScanType: string {
    case UpperArch = 'upper_arch';
    case LowerArch = 'lower_arch';
    case Bite = 'bite';
    case FullMouth = 'full_mouth';
    public function label(): string { return match($this) { self::UpperArch => 'Upper Arch', self::LowerArch => 'Lower Arch', self::Bite => 'Bite', self::FullMouth => 'Full Mouth' }; }
    public static function values(): array { return array_column(self::cases(), 'value'); }
}
