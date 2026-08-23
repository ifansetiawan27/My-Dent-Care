<?php

declare(strict_types=1);

namespace App\Platform\FileStorage\Models;

use App\Core\Base\BaseModel;

/**
 * @property string $id
 * @property string $organization_id
 * @property string|null $branch_id
 * @property string $fileable_type
 * @property string $fileable_id
 * @property string $folder
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $stored_name
 * @property string $mime_type
 * @property string $extension
 * @property int $size
 * @property string $hash
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class File extends BaseModel
{
    protected $table = 'files';

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'size' => 'integer',
        ];
    }
}
