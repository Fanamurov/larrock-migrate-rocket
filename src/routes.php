<?php

Route::group(['prefix' => 'admin'], function(){
    Route::any('/migraterocket', 'Larrock\ComponentMigrateRocket\AdminMigrateRocketController@index')->name('admin.migraterocket.index');
});