<?php

namespace Nacos\Tests;

use Nacos\NacosClient;
use Nacos\NacosConfig;

class NacosConfigTest extends TestCase
{
    public function testGetParsedConfigs()
    {
        $client = new NacosClient('192.168.13.189', 8848);

        $content = "hello=world\nabc=efg";
        $success = $client->publishConfig('config.properties', 'group_name', $content);
        self::assertTrue($success);

        sleep(1);

        $expected = ['hello' => 'world', 'abc' => 'efg'];

        $nc = new NacosConfig($client);

        $res = $nc->getParsedConfigs('config.properties', 'group_name');
        self::assertSame($expected, $res);

        $res = $nc->getParsedConfigs('config.properties', 'group_name', false);
        self::assertSame($expected, $res);
    }
}
