<?php

declare(strict_types=1);

namespace Nacos;

use Nacos\Traits\AccessToken;

class NacosAuth
{
    use AccessToken;

    /**
     * @var NacosClient
     */
    protected $client;

    /**
     * @param NacosClient $client
     */
    public function __construct(NacosClient $client)
    {
        $this->client = $client;
    }

    /**
     * @desc: login
     * @param string $username
     * @param string $password
     */
    public function login(string $username, string $password)
    {
        $res = $this->client->login($username, $password);
        $content = json_decode($res,true);
        $this->accessToken = $content['accessToken'];
        $this->expireTime = $content['tokenTtl'];
    }
}
