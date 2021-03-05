<?php

namespace nacos;

/**
 * Class NacosClientInterface
 * @package nacos
 */
interface NacosClientInterface
{
    /**
     * @param string $env
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     * @return false|string|null
     */
    public static function get($env, $dataId, $group, $tenant);

    /**
     * @param string $env
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     */
    public static function listener($env, $dataId, $group, $tenant);

    /**
     * @param string $dataId
     * @param string $group
     * @param string $content
     * @param string $tenant
     * @return bool
     */
    public static function publish($dataId, $group, $content, $tenant = "");

    /**
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     * @return bool true 删除成功
     */
    public static function delete($dataId, $group, $tenant);
}
