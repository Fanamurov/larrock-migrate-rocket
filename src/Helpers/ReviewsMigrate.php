<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\Core\Traits\AdminMethodsStore;
use Session;

class ReviewsMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = NULL;
    }

    /**
     * Выполнять после импорта пользователей!
     */
    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = \LarrockReviews::getConfig();

        $export_data = \DB::connection('migrate')->table('opinions')->get();
        foreach ($export_data as $item){
            $add_to_request = [
                'name' => $item->username,
                'contact' => $item->contact,
                'comment' => $item->text,
                'rating' => $item->stars,
                'date' => $item->date,
                'active' => $item->active,
                'position' => $item->position
            ];

            //Достаем user_id (id изменился)
            $add_to_request['user_id'] = $migrateDBLog->getNewIdByOldId($item->user_id, 'users');

            if( !empty($item->answer)){
                $add_to_request['answer'] = $item->answer;
                $add_to_request['answer_author'] = $item->answer_name;
            }

            if($item->connect_type === 'cart'){
                $add_to_request['link_name'] = $item->connect_type;
                //Достаем link_id (id изменился)
                $add_to_request['user_id'] = $migrateDBLog->getNewIdByOldId($item->cartid, 'cart');
            }

            if($item->connect_type === 'main'){
                $add_to_request['public_in_feed'] = 1;
            }

            if($item->connect_type === 'catalog'){
                if($getTovar = \LarrockCatalog::getModel()->whereUrl($item->connect_url)->first()){
                    $add_to_request['link_name'] = $item->connect_type;
                    $add_to_request['link_id'] = $getTovar->id;
                }else{
                    Session::push('message.danger', 'Не найден товар с URL: '. $item->connect_url .'. Отзыв '. $item->id .' не будет слинкован');
                }
            }

            $request = $request->merge($add_to_request);
            if($store = $this->store($request)){
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'reviews');
            }
        }
    }
}