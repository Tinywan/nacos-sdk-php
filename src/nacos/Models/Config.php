<?php

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
    public $contentMd5;

    /**
     * @var string
     */
    public $namespace;
}
