<?php


namespace nacos\exception;


use Exception;

/**
 * Class RequestUriRequiredException
 * @package nacos\exception
 */
class RequestUriRequiredException extends Exception
{
    /**
     * RequestUriRequiredException constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}