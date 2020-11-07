<?php


namespace nacos\exception;


use Exception;

/**
 * Class RequestVerbRequiredException
 * @package nacos\exception
 */
class RequestVerbRequiredException extends Exception
{
    /**
     * RequestVerbRequiredException constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}