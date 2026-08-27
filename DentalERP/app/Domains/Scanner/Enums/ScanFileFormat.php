<?php
declare(strict_types=1);
namespace App\Domains\Scanner\Enums;
enum ScanFileFormat: string {
    case Stl = 'stl';
    case Obj = 'obj';
    case Ply = 'ply';
    public function label(): string { return match($this) { self::Stl => 'STL', self::Obj => 'OBJ', self::Ply => 'PLY' }; }
    public static function values(): array { return array_column(self::cases(), 'value'); }
}
