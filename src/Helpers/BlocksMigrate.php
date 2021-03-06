<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\Core\Traits\AdminMethodsStore;

class BlocksMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = null;
    }

    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = \LarrockBlocks::getConfig();

        $export_data = \DB::connection('migrate')->table('blocks')->get();
        foreach ($export_data as $item) {
            echo '.';
            $add_to_request = [
                'title' => $item->title,
                'description' => $item->description,
                'url' => $item->url,
                'active' => $item->active,
                'position' => $item->position,
            ];

            $request = $request->merge($add_to_request);
            if ($store = $this->store($request)) {
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'blocks');

                //Добавляем медиа
                $MediaMigrate = new MediaMigrate();
                $MediaMigrate->attach($store, $item->id, 'blocks');
            }
        }
    }
}
