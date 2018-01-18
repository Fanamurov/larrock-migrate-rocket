<?php

namespace Larrock\ComponentMigrateRocket\Facades;

use Illuminate\Support\Facades\Facade;

class LarrockMigrateRocket extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'larrockmigraterocket';
    }

}