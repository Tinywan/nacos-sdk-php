<?php

namespace nacos\request\config;

/**
 * Class DeleteConfigRequest
 * @package nacos\request\config
 */
class DeleteConfigRequest extends ConfigRequest
{
    protected $uri = "/nacos/v1/cs/configs";
    protected $verb = "DELETE";
}