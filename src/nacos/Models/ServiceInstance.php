<?php

namespace Nacos\Models;

use Nacos\Exceptions\NacosException;

class ServiceInstance
{
    /**
     * 服务名，不能为空
     * @var string
     */
    public $serviceName;

    /**
     * 服务实例 IP，不能为空
     * @var string
     */
    public $ip;

    /**
     * 服务实例 port，不能为空
     * @var int
     */
    public $port;

    /**
     * 命名空间ID
     * @var string
     */
    public $namespaceId;

    /**
     * 权重
     * @var double
     */
    public $weight = 0;

    /**
     * 是否健康
     * @var boolean
     */
    public $healthy;

    /**
     * 是否上线
     * @var boolean
     */
    public $enable;

    /**
     * 扩展信息
     * @var array
     */
    public $metadata;

    /**
     * 集群名
     * @var string
     */
    public $clusterName;

    /**
     * @var bool
     */
    public $marked;

    /**
     * @var bool
     */
    public $valid;

    /**
     * @var string
     */
    public $instanceId;

    public function __construct(array $info = [])
    {
        if (isset($info['metadata']) && is_string($info['metadata'])) {
            $metadata = json_decode($info['metadata'], JSON_OBJECT_AS_ARRAY);
            if ($metadata) {
                $this->metadata = $metadata;
            }
            unset($info['metadata']);
        }

        foreach ($info as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function validate()
    {
        $this->assertNotNull('ip', $this->ip);
        $this->assertNotNull('port', $this->port);
        $this->assertNotNull('serviceName', $this->serviceName);
    }

    protected function assertNotNull($name, $value)
    {
        if (is_null($value)) {
            throw new NacosException("ServiceInstance `{$name}` cannot be null");
        }
    }

    public function toCreateParams()
    {
        return $this->filter([
            'serviceName' => $this->serviceName,
            'ip' => $this->ip,
            'port' => $this->port,
            'namespaceId' => $this->namespaceId,
            'weight' => $this->getWeightDouble(),
            'enable' => $this->enable,
            'healthy' => $this->healthy,
            'metadata' => $this->getMetadataJson(),
            'clusterName' => $this->clusterName,
        ]);
    }

    public function toDeleteParams()
    {
        return $this->filter([
            'serviceName' => $this->serviceName,
            'ip' => $this->ip,
            'port' => $this->port,
            'clusterName' => $this->clusterName,
            'namespaceId' => $this->namespaceId,
        ]);
    }

    public function toBeatParams()
    {
        return $this->filter([
            "cluster" => $this->clusterName,
            "ip" => $this->ip,
            "metadata" => $this->metadata,
            "port" => $this->port,
            "scheduled" => true,
            "serviceName" => $this->serviceName,
            "weight" => $this->weight,
        ]);
    }

    public function toUpdateParams()
    {
        return $this->filter([
            'serviceName' => $this->serviceName,
            'ip' => $this->ip,
            'port' => $this->port,
            'namespaceId' => $this->namespaceId,
            'weight' => $this->getWeightDouble(),
            'enable' => $this->enable,
            'healthy' => $this->healthy,
            'metadata' => $this->getMetadataJson(),
            'clusterName' => $this->clusterName,
        ]);
    }

    protected function getMetadataJson()
    {
        return $this->metadata ? json_encode($this->metadata) : null;
    }

    public function getWeightDouble()
    {
        return $this->weight ? doubleval($this->weight) : 0;
    }

    protected function filter(array $array)
    {
        return array_filter($array, function ($value) {
            return !is_null($value);
        });
    }
}
