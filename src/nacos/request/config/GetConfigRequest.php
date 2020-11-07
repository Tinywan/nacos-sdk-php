<?php

namespace nacos\request\config;

/**
 * Class GetConfigRequest
 * @package nacos\request\config
 */
class GetConfigRequest extends ConfigRequest
{
    protected $uri = "/nacos/v1/cs/configs";
    protected $verb = "GET";

}