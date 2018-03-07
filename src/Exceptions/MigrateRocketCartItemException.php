<?php

namespace Larrock\ComponentMigrateRocket\Exceptions;

use Exception;

class MigrateRocketCartItemException extends Exception
{
    public $status = 422;

    /**
     * Create a new validation exception from a plain array of messages.
     *
     * @param  $message
     * @return static
     */
    public static function withMessage($message)
    {
        \Log::error($message);
        return new static($message);
    }
}
