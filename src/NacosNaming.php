<?php

declare(strict_types=1);

namespace Nacos;

use Nacos\Exceptions\NacosNamingNoAliveInstance;
use Nacos\Utils\RandomByWeightSelector;

class NacosNaming
{
    /**
     * @var NacosClient
     */
    protected $client;

    /**
     * NacosNaming constructor.
     * @param NacosClient $client
     */
    public function __construct(NacosClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $serviceName
     * @return Models\ServiceInstance
     * @throws \Exception
     */
    public function selectOneHealthyInstance(string $serviceName)
    {
        $list = $this->client->getInstanceList($serviceName);

        if (count($list->hosts) === 0) {
            throw new NacosNamingNoAliveInstance("$serviceName no alive instnace");
        }

        return RandomByWeightSelector::select((array) $list->hosts);
    }

    /**
     * @return NacosClient
     */
    public function getClient(): NacosClient
    {
        return $this->client;
    }
}
