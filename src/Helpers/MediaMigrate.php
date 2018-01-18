<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\Core\Traits\AdminMethodsStore;

class MediaMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = NULL;
    }

    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = \LarrockBlocks::getConfig();

        $export_data = \DB::connection('migrate')->table('images')->get();
        foreach ($export_data as $item){
            $add_to_request = [
                'title' => $item->title,
                'description' => $item->description,
                'url' => $item->url,
                'active' => $item->active,
                'position' => $item->position
            ];

            $request = $request->merge($add_to_request);
            if($store = $this->store($request)){
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'media');
            }
        }
    }
}