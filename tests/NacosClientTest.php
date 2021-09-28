<?php

namespace Nacos\Tests;

use Nacos\Exceptions\NacosConfigNotFound;
use Nacos\Models\BeatInfo;
use Nacos\Models\BeatResult;
use Nacos\Models\Config;
use Nacos\Models\ServiceInstance;
use Nacos\Models\ServiceInstanceList;
use Nacos\NacosAuth;
use Nacos\NacosClient;
use PHPUnit\Framework\AssertionFailedError;

class NacosClientTest extends TestCase
{
    public function testConfig()
    {
        $dataId = 'nacos.php';
        $group = 'DEFAULT_GROUP';
        $value = 'test value 2';

        $client = new NacosClient('192.168.13.189', 8848);

        $publishResult = $client->publishConfig($dataId, $group, $value);
        self::assertTrue($publishResult);

        sleep(1);

        $newValue = $client->getConfig($dataId, $group);
        self::assertSame($value, $newValue);

        $removeResult = $client->removeConfig($dataId);
        self::assertTrue($removeResult);
    }

    public function testConfigNotFoundException()
    {
        $client = new NacosClient('192.168.13.189', 8848);
        $dataId = 'not-exists-data-id.' . microtime(true);

        try {
            $client->getConfig($dataId);
            self::assertTrue(false, 'Failed to throw an exception, this line should not be executed');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            self::assertInstanceOf(NacosConfigNotFound::class, $e);
        }
    }

     public function testListenConfig()
     {
         $dataId = 'nginx.conf';
         $group = 'DEFAULT_GROUP';

         $client = new NacosClient('192.168.13.189', 8848);
         $client->setTimeout(1);
         $content = $client->getConfig($dataId, $group);
         $contentMd5 = md5($content);
         $pid = pcntl_fork();
         if ($pid === 0) {
             // fork child process
             sleep(2);
             $success = $client->publishConfig($dataId, $group, 'world=hello' . microtime());
             self::assertTrue($success);
             exit; // child process exit
         }

         $cache = new Config();
         $cache->dataId = $dataId;
         $cache->group = $group;
         $cache->contentMd5 = $contentMd5;
         $result = $client->listenConfig([$cache]);
         self::assertTrue(is_array($result));
         self::assertSame($dataId, $result[0]->dataId);
         self::assertSame($group, $result[0]->group);
     }

     public function testServiceInstance()
     {
         $serviceName  = 'NacosService';
         $client = new NacosClient('192.168.13.189', 8848);

         // [2] 需要注册的服务实例
         $instance = new ServiceInstance();
         $instance->serviceName = $serviceName;
         $instance->enable = true;
         $instance->ip = '192.168.13.189';
         $instance->port = 8787;
         $instance->weight = 10;
         $instance->healthy = true;
         $instance->ephemeral = false;
         $instance->metadata = [
             'hello' => 'world'
         ];

         $success = $client->createInstance($instance);
         self::assertTrue($success);

         $list = $client->getInstanceList($serviceName);
         self::assertInstanceOf(ServiceInstanceList::class, $list);
         self::assertInstanceOf(ServiceInstance::class, $list->hosts[0]);
         self::assertSame(['hello' => 'world'], $list->hosts[0]->metadata);

         $instance->metadata = ['test' => 'nacos'];
         $updateSuccess = $client->updateInstance($instance);
         self::assertTrue($updateSuccess);

         $listAfterUpdate = $client->getInstanceList('hello.world');
         self::assertSame(['test' => 'nacos'], $listAfterUpdate->hosts[0]->metadata);

         $result = $client->getInstance('hello.world', '127.0.0.1', 7777);
         self::assertInstanceOf(ServiceInstance::class, $result);

         $beat = new BeatInfo();
         $beat->ip = '192.168.13.189';
         $beat->port = 8787;
         $beatResp = $client->sendInstanceBeat('hello.world', $beat);
         self::assertInstanceOf(BeatResult::class, $beatResp);
     }

    public function testLogin()
    {
        $client = new NacosClient('192.168.13.189', 8848);
        $auth = new NacosAuth($client);
        $auth->login('nacos','nacos');

        $dataId = 'database.php';
        $group = 'DEFAULT_GROUP';
        $value = '2021';

        $client = new NacosClient('192.168.13.189', 8848);
        $newValue = $client->getConfig($dataId, $group);
        self::assertSame($value, $newValue);
    }
}
