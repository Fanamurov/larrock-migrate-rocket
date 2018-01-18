<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\ComponentMigrateRocket\Exceptions\MigrateRocketCategoryEmptyException;
use Larrock\Core\Traits\AdminMethodsStore;

class CategoryMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = NULL;
    }

    /**
     * @throws MigrateRocketCategoryEmptyException
     */
    public function import()
    {
        $this->config = \LarrockCategory::getConfig();

        for($i=0; $i < 4; $i++){
            $export_data = \DB::connection('migrate')->table('category')->where('level', '=', $i)->get();
            $this->importLevel($export_data);
        }
    }

    /**
     * @param $export_data
     * @throws MigrateRocketCategoryEmptyException
     */
    public function importLevel($export_data)
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        foreach ($export_data as $item){
            $add_to_request = [
                'title' => $item->title,
                'description' => $item->description,
                'component' => $item->type,
                'level' => ++$item->level,
                'url' => $item->url,
                'active' => $item->active,
                'sitemap' => 1,
                'rss' => 1,
                'position' => $item->position
            ];

            if($item->parent === 0){
                $add_to_request['parent'] = null;
            }else{
                //Достаем parent (id изменился)
                if( !$add_to_request['parent'] = $migrateDBLog->getNewIdByOldId($item->parent, 'category')){
                    throw new MigrateRocketCategoryEmptyException('Category parent in '. $this->config->name .' not may be empty. '. json_encode($item));
                }
            }

            $request = $request->merge($add_to_request);
            if($store = $this->store($request)){
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'category');
            }
        }
    }
}