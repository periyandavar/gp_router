<?php

namespace Router\Exception;

use Exception;

class RouterException extends Exception
{
    public const UNKNOWN_ERROR = 100;
    public const CONTROLLER_NOT_FOUND_ERROR = 101;

    public function __construct($message = '', $code = 0, ?Exception $previous = null)
    {
        $code = $code === 0 ? 100 : $code;
        parent::__construct($message, $code, $previous);
    }
}
