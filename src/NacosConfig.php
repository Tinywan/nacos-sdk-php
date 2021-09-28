<?php

declare(strict_types=1);

namespace Nacos;

use \UnexpectedValueException;
use Nacos\Utils\PropertiesConfigParser;

class NacosConfig
{
    /**
     * @var NacosClient
     */
    protected $client;

    /**
     * NacosConfig constructor.
     * @param NacosClient $client
     */
    public function __construct(NacosClient $client)
    {
        $this->client = $client;
    }

    /**
     * @desc: 获取配置内容并解析(现仅支持 properties 格式)
     * @param string $dataId
     * @param string $group
     * @param string $format
     * @return array
     * @author Tinywan(ShaoBo Wan)
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
        throw new UnexpectedValueException('Format not supported');
    }

    /**
     * @return NacosClient
     */
    public function getClient(): NacosClient
    {
        return $this->client;
    }
}
