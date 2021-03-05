<?php

namespace nacos;

use Exception;
use nacos\exception\InvalidConfigException;
use nacos\util\LogUtil;
use nacos\request\config\GetConfigRequest;
use nacos\failover\LocalConfigInfoProcessor;
use nacos\request\config\DeleteConfigRequest;
use nacos\request\config\PublishConfigRequest;
use nacos\request\config\ListenerConfigRequest;
use nacos\listener\config\ListenerConfigRequestErrorListener;

/**
 * Class NacosClient
 * @package nacos
 */
class NacosClient implements NacosClientInterface
{
    /**
     * Undocumented function
     *
     * @param string $env
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     *
     * @return void
     */
    public static function listener($env, $dataId, $group, $tenant = "")
    {
        $snapshotFile = LocalConfigInfoProcessor::getSnapshotFile($env, $dataId, $group, $tenant);
        $loop = 0;
        do {
            $loop++;

            $listenerConfigRequest = new ListenerConfigRequest();
            $listenerConfigRequest->setDataId($dataId);
            $listenerConfigRequest->setGroup($group);
            $listenerConfigRequest->setTenant($tenant);
            $md5 = '';
            if (file_exists($snapshotFile)) {
                $md5 = md5(file_get_contents($snapshotFile));
            }
            $listenerConfigRequest->setContentMD5($md5);

            try {
                $response = $listenerConfigRequest->doRequest();
                if ($response->getBody()->getContents()) {
                    $config = self::get($env, $dataId, $group, $tenant);
                    LogUtil::info("found changed config: " . $config);
                    LocalConfigInfoProcessor::saveSnapshot($env, $dataId, $group, $tenant, $config, $snapshotFile);
                }
            } catch (Exception $e) {
                throw new InvalidConfigException('This is Config Invalid ' . $e->getMessage());
                ListenerConfigRequestErrorListener::notify($env, $dataId, $group, $tenant);
                // 短暂休息会儿
                usleep(500);
            }
            LogUtil::info("listener loop count: " . $loop);
        } while (true);
    }

    /**
     * Undocumented function
     *
     * @param string $env
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     *
     * @return mixed
     */
    public static function get($env, $dataId, $group, $tenant)
    {
        $getConfigRequest = new GetConfigRequest();
        $getConfigRequest->setDataId($dataId);
        $getConfigRequest->setGroup($group);
        $getConfigRequest->setTenant($tenant);

        try {
            $response = $getConfigRequest->doRequest();
            $config = $response->getBody()->getContents();
            LocalConfigInfoProcessor::saveSnapshot($env, $dataId, $group, $tenant, $config);
        } catch (Exception $e) {
            throw new InvalidConfigException('This is Config Invalid ' . $e->getMessage());
            // $config = LocalConfigInfoProcessor::getFailover($env, $dataId, $group, $tenant);
            // $config = $config ? $config
            //     : LocalConfigInfoProcessor::getSnapshot($env, $dataId, $group, $tenant);
            // $configListenerParameter = Config::of($env, $dataId, $group, $tenant, $config);
            // GetConfigRequestErrorListener::notify($configListenerParameter);
            // if ($configListenerParameter->isChanged()) {
            //     $config = $configListenerParameter->getConfig();
            // }
        }

        return $config;
    }

    /**
     * Undocumented function
     *
     * @param string $dataId
     * @param string $group
     * @param string $content
     * @param string $tenant
     *
     * @return mixed
     */
    public static function publish($dataId, $group, $content, $tenant = "")
    {
        $publishConfigRequest = new PublishConfigRequest();
        $publishConfigRequest->setDataId($dataId);
        $publishConfigRequest->setGroup($group);
        $publishConfigRequest->setTenant($tenant);
        $publishConfigRequest->setContent($content);

        try {
            $response = $publishConfigRequest->doRequest();
        } catch (Exception $e) {
            return false;
        }
        return $response->getBody()->getContents() == "true";
    }

    /**
     * del
     *
     * @param string $dataId
     * @param string $group
     * @param string $tenant
     *
     * @return void
     */
    public static function delete($dataId, $group, $tenant)
    {
        $deleteConfigRequest = new DeleteConfigRequest();
        $deleteConfigRequest->setDataId($dataId);
        $deleteConfigRequest->setGroup($group);
        $deleteConfigRequest->setTenant($tenant);

        $response = $deleteConfigRequest->doRequest();
        return $response->getBody()->getContents() == "true";
    }
}
