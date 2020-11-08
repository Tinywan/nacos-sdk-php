<?php

namespace nacos\exception;

use Exception;

class InvalidConfigException extends Exception
{
    /**
     * Bootstrap.
     *
     * @param string       $message
     */
    public function __construct($message)
    {
        parent::__construct('INVALID_CONFIG: '.$message);
    }
}