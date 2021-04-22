<?php

declare(strict_types=1);

namespace Nacos;

use Nacos\Exceptions\NacosException;
use Nacos\Utils\PropertiesConfigParser;

class NacosConfig
{
    /**
     * @var NacosClient
     */
    protected $client;

    public function __construct(NacosClient $client)
    {
        $this->client = $client;
    }

    /**
     * 获取配置内容并解析，现仅支持 properties 格式
     *
     * @param string $dataId
     * @param string $group
     * @param string $format
     * @return array
     */
    public function getParsedConfigs(
        string $dataId,
        string $group = NacosClient::DEFAULT_GROUP,
        string $format = 'properties'
    ) {
        $content = $this->client->getConfig($dataId, $group);

        if (!$format) {
            $format = array_slice(explode('.', $dataId), -1)[0];
        }

        if ($format === 'properties') {
            return PropertiesConfigParser::parse($content);
        }

        throw new NacosException('Unsupported config format');
    }

    /**
     * @return NacosClient
     */
    public function getClient(): NacosClient
    {
        return $this->client;
    }
}
