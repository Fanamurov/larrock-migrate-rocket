<?php

namespace Larrock\ComponentMigrateRocket\Models;

use Illuminate\Database\Eloquent\Model;

class MigrateDB extends Model
{
    public $table = 'migrate_db';

    public $fillable = ['old_id', 'new_id', 'table_name'];

    protected $casts = [
        'old_id' => 'integer',
        'new_id' => 'integer'
    ];
}