<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\ComponentMigrateRocket\Exceptions\MigrateRocketCategoryEmptyException;
use Larrock\Core\Traits\AdminMethodsStore;

class CatalogMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = NULL;
    }

    /**
     * Выполнять после импорта пользователей!
     * @throws MigrateRocketCategoryEmptyException
     */
    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = \LarrockCatalog::getConfig();

        $export_data = \DB::connection('migrate')->table('catalog')->get();
        foreach ($export_data as $item){
            $add_to_request = [
                'title' => $item->title,
                'short' => $item->description,
                'description' => $item->description_big,
                'url' => $item->url,
                'what' => $item->what,
                'cost' => $item->cost,
                'cost_old' => $item->cost_old,
                'manufacture' => $item->manufacture,
                'active' => $item->active,
                'position' => $item->position,
                'articul' => $item->articul,
                'label_sale' => $item->label_sale,
                'label_new' => $item->label_new,
                'label_popular' => $item->label_hot,
            ];

            $add_to_request['category'] = $migrateDBLog->getNewIdByOldId($item->category, 'category');

            if( !$add_to_request['category']){
                throw new MigrateRocketCategoryEmptyException('Category in '. $this->config->name .' not may be empty. '. json_encode($item));
                //return FALSE;
            }

            //nalichie, offer, label_offer, label_main, position_index, label_buket_dnay, kolvomono, vidarange, modifycostvidarange, modifycostkolvomono
            //soputka, povod, razmer, colors, akcia, todaydelivery, free

            $request = $request->merge($add_to_request);
            if($store = $this->store($request)){
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'catalog');
            }
        }
    }
}