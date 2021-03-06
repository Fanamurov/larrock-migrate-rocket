<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\Core\Traits\AdminMethodsStore;
use Larrock\ComponentMigrateRocket\Exceptions\MigrateRocketCategoryEmptyException;

class FeedMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = null;
    }

    /**
     * @throws MigrateRocketCategoryEmptyException
     */
    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = \LarrockFeed::getConfig();

        $export_data = \DB::connection('migrate')->table('feed')->get();
        foreach ($export_data as $item) {
            echo '.';
            $add_to_request = [
                'title' => $item->title,
                'short' => $item->short,
                'description' => $item->description,
                'date' => $item->date,
                'url' => $item->url,
                'active' => $item->active,
                'position' => $item->position,
            ];

            //Достаем parent (id изменился)
            $add_to_request['category'] = $migrateDBLog->getNewIdByOldId($item->category, 'category');

            if (! $add_to_request['category']) {
                throw new MigrateRocketCategoryEmptyException('Category in '.$this->config->name.' not may be empty. '.json_encode($item));
            }

            $request = $request->merge($add_to_request);
            if ($store = $this->store($request)) {
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'feed');

                //Добавляем медиа
                $MediaMigrate = new MediaMigrate();
                $MediaMigrate->attach($store, $item->id, 'feed');
            }
        }
    }
}
