<?php

namespace nacos;

use nacos\util\LogUtil;

/**
 * Class Nacos
 * @package nacos
 */
class Client
{
    private static $clientClass;

    /**
     * init
     *
     * @param string $host
     * @param string $env
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     *
     * @return void
     */
    public static function init($host, $env, $dataId, $group, $tenant)
    {
        static $client;
        if ($client == null) {
            NacosConfig::setHost($host);
            NacosConfig::setEnv($env);
            NacosConfig::setDataId($dataId);
            NacosConfig::setGroup($group);
            NacosConfig::setTenant($tenant);

            if (getenv("NACOS_ENV") == "local") {
                LogUtil::info("nacos run in dummy mode");
                self::$clientClass = DummyNacosClient::class;
            } else {
                self::$clientClass = NacosClient::class;
            }
            $client = new self();
        }
        return $client;
    }

    /**
     * run once
     *
     * @return void
     */
    public function runOnce()
    {
        return call_user_func_array([self::$clientClass, "get"], [
            NacosConfig::getEnv(),
            NacosConfig::getDataId(),
            NacosConfig::getGroup(),
            NacosConfig::getTenant()
        ]);
    }

    /**
     * loop listener
     *
     * @return self
     */
    public function listener()
    {
        call_user_func_array([self::$clientClass, "listener"], [
            NacosConfig::getEnv(),
            NacosConfig::getDataId(),
            NacosConfig::getGroup(),
            NacosConfig::getTenant()
        ]);
        return $this;
    }
}
