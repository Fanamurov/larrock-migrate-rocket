<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\Core\Traits\AdminMethodsStore;

class MenuMigrate
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

        $this->config = \LarrockMenu::getConfig();

        $export_data = \DB::connection('migrate')->table('menu')->get();
        foreach ($export_data as $item){
            $add_to_request = [
                'title' => $item->title,
                'type' => $item->type,
                'parent' => $item->parent,
                'url' => $item->url,
                'active' => $item->active,
                'position' => $item->position
            ];

            if(empty($item->type)){
                $add_to_request['type'] = 'default';
            }

            $request = $request->merge($add_to_request);
            if($store = $this->store($request)){
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'menu');
            }
        }
    }
}