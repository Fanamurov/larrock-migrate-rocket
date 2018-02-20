<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\ComponentMigrateRocket\Exceptions\MigrateRocketCategoryEmptyException;
use Larrock\Core\Models\Link;
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
            echo '.';
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
                'label_main' => $item->label_main,
                'label_buket_dnay' => $item->label_buket_dnay,
                'position_index' => $item->position_index,
                'delivery' => $item->nalichie,
                'razmer' => $item->razmer,
                'akcia' => $item->akcia,
                'todaydelivery' => $item->todaydelivery,
                'free' => $item->free,
                'povod' => $item->povod,
                'colors' => $item->colors,

                'kolvomono' => $item->kolvomono,
                'vidarange' => $item->vidarange,
                'modifycostvidarange' => $item->modifycostvidarange,
                'modifycostkolvomono' => $item->modifycostkolvomono,
            ];

            $add_to_request['category'] = $migrateDBLog->getNewIdByOldId($item->category, 'category');

            if( !$add_to_request['category']){
                throw new MigrateRocketCategoryEmptyException('Category in '. $this->config->name .' not may be empty. '. json_encode($item));
            }

            //kolvomono, vidarange, modifycostvidarange, modifycostkolvomono, soputka

            $request = $request->merge($add_to_request);
            if($store = $this->store($request)){
                $this->importSerializedParamRow($store->id, $add_to_request);
                $this->importParamRow($store->id, $add_to_request);
                $this->importCostParamRow($store->id, $add_to_request);

                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'catalog');

                //Есть группы товаров. Бывает так, что медиа навешаны на не импортируемый товар из group (импортирован первый)
                $item_media_id = null;
                $get_group = \DB::connection('migrate')->table('catalog')->where('group', '=', $item->group)->get();
                foreach ($get_group as $group_item){
                    if($export_data = \DB::connection('migrate')->table('images')
                        ->where('type_connect', '=', 'catalog')
                        ->where('id_connect', '=', $group_item->id)->first()){
                        if(isset($export_data->id_connect)){
                            $item_media_id = $export_data->id_connect;
                        }
                    }
                }

                if( !$item_media_id){
                    if($export_data = \DB::connection('migrate')->table('files')
                        ->where('type_connect', '=', 'catalog')
                        ->where('id_connect', '=', $group_item->id)->first()){
                        if(isset($export_data->id_connect)){
                            $item_media_id = $export_data->id_connect;
                        }
                    }
                }

                if($item_media_id){
                    //Добавляем медиа
                    $MediaMigrate = new MediaMigrate();
                    $MediaMigrate->attach($store, $item_media_id, 'catalog');
                }
            }
        }
    }

    public function importCostParamRow($store_id, $data)
    {
        $paramsRow = ['kolvomono', 'vidarange'];
        foreach ($paramsRow as $row){
            //$config_row = $this->config->rows[$row];
            $config_row = $this->config->rows['param'];
            $model_row = new $config_row->modelChild;
            if(@unserialize($data[$row]) !== FALSE){
                $values = unserialize($data[$row]);
                if($values && is_array($values)){
                    foreach ($values as $value){
                        //kolvomono/Стандартно/700
                        $explode = explode('/', $value);
                        if(array_key_exists(2, $explode)){
                            //Проверяем наличие тэга в БД
                            if( !$tag = $model_row->whereTitle($explode[1])->first()){
                                $model_row = new $config_row->modelChild;
                                $model_row->title = $explode[1];
                                $model_row->save();
                                $tag = $model_row;
                                //echo 'SAVE ';
                            }

                            //echo $tag->title;

                            //Создаем связь
                            $model = new Link();
                            $model->id_parent = $store_id;
                            $model->model_parent = $config_row->modelParent;
                            $model->model_child = $config_row->modelChild;
                            $model->id_child = $tag->id;
                            $model->cost = $explode[2];
                            $model->save();
                        }
                    }
                }
            }
        }
    }

    public function importSerializedParamRow($store_id, $data)
    {
        $paramsRow = ['povod', 'colors'];
        foreach ($paramsRow as $row){
            $config_row = $this->config->rows[$row];
            $model_row = new $config_row->modelChild;
            if(@unserialize($data[$row]) !== FALSE){
                $values = unserialize($data[$row]);
                foreach ($values as $value){
                    if( !empty($value)){
                        //Проверяем наличие тэга в БД
                        if( !$tag = $model_row->whereTitle($value)->first()){
                            $model_row = new $config_row->modelChild;
                            $model_row->title = $value;
                            $model_row->save();
                            $tag = $model_row;
                            //echo 'SAVE ';
                        }

                        //echo $tag->title;

                        //Создаем связь
                        $model = new Link();
                        $model->id_parent = $store_id;
                        $model->model_parent = $config_row->modelParent;
                        $model->model_child = $config_row->modelChild;
                        $model->id_child = $tag->id;
                        $model->save();
                    }
                }
            }
        }
    }

    public function importParamRow($store_id, $data)
    {
        $paramsRow = ['delivery'];
        foreach ($paramsRow as $row){
            $config_row = $this->config->rows[$row];
            $model_row = new $config_row->modelChild;
            $value = $data[$row];
            if( !empty($value)){
                //Проверяем наличие тэга в БД
                if( !$tag = $model_row->whereTitle($value)->first()){
                    $model_row = new $config_row->modelChild;
                    $model_row->title = $value;
                    $model_row->save();
                    $tag = $model_row;
                    //echo 'SAVE ';
                }

                //echo $tag->title;

                //Создаем связь
                $model = new Link();
                $model->id_parent = $store_id;
                $model->model_parent = $config_row->modelParent;
                $model->model_child = $config_row->modelChild;
                $model->id_child = $tag->id;
                $model->save();
            }
        }
    }
}