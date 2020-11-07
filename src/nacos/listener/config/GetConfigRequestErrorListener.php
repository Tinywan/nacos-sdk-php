<?php


namespace nacos\listener\config;


use nacos\listener\Listener;

class GetConfigRequestErrorListener extends Listener
{
    /**
     * @var array 观察者数组
     */
    protected static $observers = array();
}