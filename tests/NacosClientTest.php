<?php // zhangwei@dankegongyu.com 

namespace Nacos\Tests;

use Nacos\Exceptions\NacosConfigNotFound;
use Nacos\Models\BeatInfo;
use Nacos\Models\BeatResult;
use Nacos\Models\Config;
use Nacos\Models\ServiceInstance;
use Nacos\Models\ServiceInstanceList;
use Nacos\NacosClient;
use PHPUnit\Framework\AssertionFailedError;

class NacosClientTest extends TestCase
{
    public function testConfig()
    {
        $dataId = 'nacos.php';
        $group = 'DEFAULT_GROUP';
        $value = 'test value';

        $client = new NacosClient('dnmp-nacos', 8848);

        $publishResult = $client->publishConfig($dataId, $group, $value);
        self::assertTrue($publishResult);

        sleep(1);

        $newValue = $client->getConfig($dataId, $group);
        self::assertSame($value, $newValue);

        $removeResult = $client->removeConfig($dataId);
        self::assertTrue($removeResult);
    }

    // public function testConfigNotFoundException()
    // {
    //     $client = new NacosClient('localhost', 8848);

    //     $dataId = 'not-exists-data-id.' . microtime(true);

    //     try {
    //         $client->getConfig($dataId);
    //         self::assertTrue(false, '抛出异常失败，此行不应执行');
    //     } catch (AssertionFailedError $e) {
    //         throw $e;
    //     } catch (\Throwable $e) {
    //         self::assertInstanceOf(NacosConfigNotFound::class, $e);
    //     }
    // }

    // public function testListenConfig()
    // {
    //     $dataId = 'abc';
    //     $group = 'test-group';

    //     $client = new NacosClient('localhost', 8848);
    //     $client->setTimeout(1);
    //     $content = $client->getConfig($dataId, $group);
    //     $contentMd5 = md5($content);

    //     $pid = pcntl_fork();
    //     if ($pid === 0) {
    //         // 子进程更新配置
    //         sleep(2);
    //         $success = $client->publishConfig($dataId, $group, 'world=hello' . microtime());
    //         self::assertTrue($success);
    //         exit; // 子进程退出
    //     }

    //     $cache = new Config();
    //     $cache->dataId = $dataId;
    //     $cache->group = $group;
    //     $cache->contentMd5 = $contentMd5;
    //     $result = $client->listenConfig([$cache]);
    //     self::assertTrue(is_array($result));
    //     self::assertSame('abc', $result[0]->dataId);
    //     self::assertSame('test-group', $result[0]->group);
    // }

    // public function testServiceInstance()
    // {
    //     $client = new NacosClient('localhost', 8848);

    //     $instance = new ServiceInstance();
    //     $instance->ip = '127.0.0.1';
    //     $instance->port = 7777;
    //     $instance->serviceName = 'hello.world';
    //     $instance->metadata = ['hello' => 'world'];

    //     $success = $client->createInstance($instance);
    //     self::assertTrue($success);

    //     $list = $client->getInstanceList('hello.world');
    //     self::assertInstanceOf(ServiceInstanceList::class, $list);
    //     self::assertInstanceOf(ServiceInstance::class, $list->hosts[0]);
    //     self::assertSame(['hello' => 'world'], $list->hosts[0]->metadata);

    //     $instance->metadata = ['test' => 'zhwei'];
    //     $updateSuccess = $client->updateInstance($instance);
    //     self::assertTrue($updateSuccess);

    //     $listAfterUpdate = $client->getInstanceList('hello.world');
    //     self::assertSame(['test' => 'zhwei'], $listAfterUpdate->hosts[0]->metadata);

    //     $result = $client->getInstance('hello.world', '127.0.0.1', 7777);
    //     self::assertInstanceOf(ServiceInstance::class, $result);

    //     $beat = new BeatInfo();
    //     $beat->ip = '127.0.0.1';
    //     $beat->port = 1234;
    //     $beatResp = $client->sendInstanceBeat('hello.world', $beat);
    //     self::assertInstanceOf(BeatResult::class, $beatResp);
    // }
}
