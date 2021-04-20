<?php

namespace Nacos\Models;

class ServiceInstanceList
{

    /**
     * @var string
     */
    public $dom;

    /**
     * @var array
     */
    public $metadata = [];

    /**
     * @var int
     */
    public $cacheMillis;

    /**
     * @var bool
     */
    public $useSpecifiedURL;

    /**
     * @var ServiceInstance[]
     */
    public $hosts = [];

    /**
     * @var string
     */
    public $checksum;

    /**
     * @var int
     */
    public $lastRefTime;

    /**
     * @var string
     */
    public $env;

    /**
     * @var string
     */
    public $clusters;

    public function __construct(array $data = [])
    {
        if (empty($data)) {
            return;
        }

        if (isset($data['hosts'])) {
            foreach ($data['hosts'] as $host) {
                $this->hosts[] = new ServiceInstance($host);
            }
            unset($data['hosts']);
        }

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
