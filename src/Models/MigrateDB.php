<?php

namespace Larrock\ComponentMigrateRocket\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $old_id
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property \Carbon\Carbon $updated_at
 * @property int $new_id
 */
class MigrateDB extends Model
{
    public $table = 'migrate_db';

    public $fillable = ['old_id', 'new_id', 'table_name'];

    protected $casts = [
        'old_id' => 'integer',
        'new_id' => 'integer',
    ];
}
