<?php

namespace Nacos\Tests;

use Nacos\Exceptions\NacosNamingNoAliveInstance;
use Nacos\Exceptions\NacosNamingNotFound;
use Nacos\Models\BeatInfo;
use Nacos\Models\ServiceInstance;
use Nacos\NacosClient;
use Nacos\NacosNaming;
use PHPUnit\Framework\AssertionFailedError;

class NacosNamingTest extends TestCase
{
    public function testSelectOneHealthyInstance()
    {
        $client = new NacosClient('192.168.13.189', 8848);

        $instance = new ServiceInstance();
        $instance->serviceName = 'hello.world';
        $instance->ip = '127.0.0.1';
        $instance->port = 7777;
        $instance->healthy = true;
        $success = $client->createInstance($instance);
        self::assertTrue($success);

        $beat = new BeatInfo();
        $beat->ip = $instance->ip;
        $beat->serviceName = $instance->serviceName;
        $beat->port = $instance->port;
        $client->sendInstanceBeat('hello.world', $beat);

        sleep(1);

        $naming = new NacosNaming($client);
        $instance = $naming->selectOneHealthyInstance('hello.world');
        self::assertInstanceOf(ServiceInstance::class, $instance);
        self::assertSame('hello.world', $instance->serviceName);

        try {
            $naming->selectOneHealthyInstance('hello.world.not-exists');
            self::assertTrue(false, 'Failed to throw an exception, this line should not be executed');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            self::assertInstanceOf(NacosNamingNotFound::class, $e);
        }

        // test NacosNamingNoAliveInstance exception
        $instance->serviceName = 'hello.another-world';
        $success = $client->createInstance($instance);
        self::assertTrue($success);
        $success = $client->deleteInstance($instance->serviceName, $instance->ip, $instance->port);
        self::assertTrue($success);
        try {
            $naming->selectOneHealthyInstance('hello.another-world');
            self::assertTrue(false, 'Failed to throw an exception, this line should not be executed');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            self::assertInstanceOf(NacosNamingNoAliveInstance::class, $e);
        }
    }
}
