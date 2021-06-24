<?php

declare(strict_types=1);

namespace Nacos\Models;

use Nacos\NacosClient;

class Config
{
    /**
     * @var string
     */
    public $dataId;

    /**
     * @var string
     */
    public $group = NacosClient::DEFAULT_GROUP;

    /**
     * @var string
     */
    public $namespace;

    /**
     * @var string
     */
    public $contentMd5;
}
