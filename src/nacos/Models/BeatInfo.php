<?php

declare(strict_types=1);

namespace Nacos\Models;

class BeatInfo implements \JsonSerializable
{
    /**
     * @var int
     */
    public $port;

    /**
     * @var string
     */
    public $ip;

    /**
     * @var double
     */
    public $weight;

    /**
     * @var string
     */
    public $serviceName;

    /**
     * @var string
     */
    public $cluster;

    /**
     * @var array
     */
    public $metadata;

    /**
     * @var bool
     */
    public $scheduled;

    public function jsonSerialize()
    {
        return array_filter([
            'port' => $this->port,
            'ip' => $this->ip,
            'weight' => $this->weight,
            'serviceName' => $this->serviceName,
            'cluster' => $this->cluster,
            'metadata' => $this->metadata,
            'scheduled' => $this->scheduled,
        ], function ($value) {
            return !is_null($value);
        });
    }
}
