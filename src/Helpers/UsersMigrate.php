<?php

namespace Larrock\ComponentMigrateRocket\Helpers;

use Illuminate\Http\Request;
use Larrock\ComponentMigrateRocket\Models\MigrateDB;
use Larrock\ComponentUsers\Facades\LarrockUsers;
use Larrock\ComponentUsers\Models\RoleUsers;
use Larrock\Core\Traits\AdminMethodsStore;

class UsersMigrate
{
    use AdminMethodsStore;

    public function __construct()
    {
        $this->allow_redirect = NULL;
    }

    /**
     * Импорт пользователей
     */
    public function import()
    {
        $request = new Request();
        $migrateDBLog = new MigrateDBLog();

        $this->config = \LarrockUsers::getConfig();

        $export_data = \DB::connection('migrate')->table('users')->get();
        foreach ($export_data as $item){
            $add_to_request = [
                'name' => $item->username,
                'email' => $item->email,
                'password' => $item->password, //TODO
                'fio' => $item->username_alias,
                'address' => $item->address,
                'tel' => $item->phone,
                'position' => $item->position
            ];

            $request = $request->merge($add_to_request);

            if( !LarrockUsers::getModel()->whereEmail($item->email)->first() && $store = $this->store($request)){
                //Ведем лог изменений id
                $migrateDBLog->log($item->id, $store->id, 'users');

                //Добавляем медиа
                $MediaMigrate = new MediaMigrate();
                $MediaMigrate->attach($store, $item->id, 'users');
                $this->importUserRole($item->id, $store->id);
            }
        }
    }

    /**
     * Аттач ролей
     * @param $itemId
     * @param $storeId
     * @return bool
     */
    protected function importUserRole($itemId, $storeId)
    {
        $role = 1;
        $export_data = \DB::connection('migrate')->table('roles_users')->where('user_id', '=', $itemId)->get();
        foreach ($export_data as $key => $item){
            if($item->role_id > $role){
                $role = (integer)$item->role_id;
            }
        }

        //В старой БД 1 = user, 2 = admin. В новой 1 = admin, 3 = user
        if($role === 2){
            $role = 1;
        }else{
            $role = 3;
        }

        $roleUser = new RoleUsers();
        $roleUser->role_id = $role;
        $roleUser->user_id = $storeId;
        $roleUser->save();

        return TRUE;
    }
}