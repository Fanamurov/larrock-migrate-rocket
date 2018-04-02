<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Larrock\ComponentMigrateRocket\Models\MigrateDB;

class MigrateDBLog
{
    public function log($itemId, $storeId, $table)
    {
        $migrateDB = new MigrateDB();
        $migrateDB->old_id = $itemId;
        $migrateDB->new_id = $storeId;
        $migrateDB->table_name = $table;

        return $migrateDB->save();
    }

    public function getNewIdByOldId($oldId, $table)
    {
        if ($data = MigrateDB::whereTableName($table)->whereOldId($oldId)->first()) {
            return $data->new_id;
        }

        return null;
    }

    public function clearByTable($table)
    {
        return MigrateDB::whereTableName($table)->delete();
    }

    public function clearAll()
    {
        return MigrateDB::truncate();
    }
}
