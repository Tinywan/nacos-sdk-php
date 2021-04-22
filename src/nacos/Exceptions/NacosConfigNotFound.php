<?php

declare(strict_types=1);

namespace Nacos\Exceptions;

class NacosConfigNotFound extends NacosRequestException
{
    protected $code = 404;
}
