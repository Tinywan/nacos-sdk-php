<?php

namespace Nacos\Exceptions;

class NacosConfigNotFound extends NacosRequestException
{
    protected $code = 404;
}
