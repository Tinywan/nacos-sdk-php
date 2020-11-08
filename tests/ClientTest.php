<?php

namespace test;

use nacos\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    public function testRunOnce()
    {
        Client::init(
            "http://192.168.2.108:8848/",
            "dev",
            "redis.php",
            "DEFAULT_GROUP",
            "4b5ca7ac-3e2a-4456-a15f-f04738345699"
        )->runOnce();
    }

    public function testListener()
    {
        Client::init(
            "http://192.168.2.108:8848/",
            "dev",
            "redis.php",
            "DEFAULT_GROUP",
            "4b5ca7ac-3e2a-4456-a15f-f04738345699"
        )->listener();
    }
}
