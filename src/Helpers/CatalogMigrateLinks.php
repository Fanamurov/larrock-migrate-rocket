<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Larrock\Core\Models\Link;
use Larrock\Core\Traits\AdminMethodsStore;
use Larrock\ComponentMigrateRocket\Models\MigrateDB;

class CatalogMigrateLinks
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = null;
    }

    /**
     * Импорт сопутствующих товаров каталога (и другие связи товар-товар).
     */
    public function import()
    {
        $this->config = \LarrockCatalog::getConfig();
        $export_data = \DB::connection('migrate')->table('catalog')->get();
        $this->importSoputka($export_data);
    }

    /**
     * Импорт связей товара к товарам (например: сопутка).
     * @param $data_export
     * @param array $paramsRow
     */
    public function importSoputka($data_export, $paramsRow = ['soputka'])
    {
        foreach ($data_export as $data) {
            echo 'S';
            foreach ($paramsRow as $row) {
                if (@unserialize($data->{$row}) !== false) {
                    $values = unserialize($data->{$row});
                    foreach ($values as $value) {
                        //Значения - это артикулы товаров
                        if (! empty($value)) {
                            //Проверяем наличие товара с таким артикулом в БД
                            if ($linked = \LarrockCatalog::getModel()->whereArticul($value)->first()) {
                                if ($tovar = MigrateDB::whereOldId($data->id)->whereTableName('catalog')->first()) {
                                    //Создаем связь
                                    $model = new Link();
                                    $model->id_parent = $tovar->new_id;
                                    $model->model_parent = \LarrockCatalog::getModelName();
                                    $model->model_child = \LarrockCatalog::getModelName();
                                    $model->id_child = $linked->id;
                                    $model->save();
                                } else {
                                    \Log::error('Для товара '.$data->id.' не прикреплены сопутствующие товары. Не найдено данных в MigrateDB');
                                }
                            } else {
                                \Log::error('Товар с артикулом '.$value.' не найден в БД');
                            }
                        }
                    }
                }
            }
        }
    }
}
