<?php

declare(strict_types=1);

namespace Nacos;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Nacos\Exceptions\NacosConfigNotFound;
use Nacos\Exceptions\NacosConnectionException;
use Nacos\Exceptions\NacosNamingNotFound;
use Nacos\Exceptions\NacosRequestException;
use Nacos\Exceptions\NacosResponseException;
use Nacos\Models\BeatInfo;
use Nacos\Models\BeatResult;
use Nacos\Models\Config;
use Nacos\Models\ServiceInstance;
use Nacos\Models\ServiceInstanceList;

class NacosClient
{
    const DEFAULT_PORT = 8848;
    const DEFAULT_TIMEOUT = 3;

    const DEFAULT_GROUP = 'DEFAULT_GROUP';

    const WORD_SEPARATOR = "\x02";
    const LINE_SEPARATOR = "\x01";

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var int
     */
    protected $timeout = self::DEFAULT_TIMEOUT;

    /**
     * __construct function
     *
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param string $namespace
     * @return self
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @desc: request function
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     * @author Tinywan(ShaoBo Wan)
     */
    protected function request(string $method, string $uri, array $options = [])
    {
        if (!isset($options['timeout'])) {
            $options['timeout'] = $this->timeout;
        }

        $client = new Client([
            'base_uri' => "http://{$this->host}:{$this->port}",
            'timeout' => $this->timeout
        ]);

        try {
            $resp = $client->request($method, $uri, $options);
        } catch (ConnectException $connectException) {
            throw new NacosConnectionException("[Nacos Server] " . $connectException->getMessage());
        } catch (RequestException $exception) {
            throw new NacosRequestException($exception->getMessage());
        }
        if (404 === $resp->getStatusCode()) {
            throw new NacosConfigNotFound($resp->getReasonPhrase());
        }
        return $resp;
    }

    /**
     * @desc: login
     * @param string $username
     * @param string $password
     * @return string
     */
    public function login(string $username, string $password)
    {
        $query = [
            'username' => $username,
            'password' => $password
        ];

        $resp = $this->request('POST', '/nacos/v1/auth/users/login', [
            'http_errors' => false,
            'query' => $query
        ]);

        if (404 === $resp->getStatusCode()) {
            throw new NacosConfigNotFound(
                "config not found, username:{$username} password:{$password}",
                404
            );
        }
        return $resp->getBody()->getContents();
    }

    /**
     * Get Config Option
     * 
     * @param string $dataId
     * @param string $group
     * @return string
     * @throws NacosConfigNotFound
     */
    public function getConfig(string $dataId, string $group = self::DEFAULT_GROUP)
    {
        $query = [
            'dataId' => $dataId,
            'group' => $group
        ];

        if ($this->namespace) {
            $query['tenant'] = $this->namespace;
        }

        $resp = $this->request('GET', '/nacos/v1/cs/configs', [
            'http_errors' => false,
            'query' => $query
        ]);

        if (404 === $resp->getStatusCode()) {
            throw new NacosConfigNotFound(
                "config not found, dataId:{$dataId} group:{$group} tenant:{$this->namespace}",
                404
            );
        }
        return $resp->getBody()->getContents();
    }

    /**
     * @desc: publish Config
     * @param string $dataId
     * @param string $group
     * @param string $content
     * @return bool|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @author Tinywan(ShaoBo Wan)
     */
    public function publishConfig(string $dataId, string $group, string $content)
    {
        $formParams = [
            'dataId' => $dataId,
            'group' => $group,
            'content' => $content
        ];

        if ($this->namespace) {
            $formParams['tenant'] = $this->namespace;
        }

        $resp = $this->request('POST', '/nacos/v1/cs/configs', ['form_params' => $formParams]);
        return $this->assertResponse($resp, 'true', "Nacos Client update config fail");
    }

    /**
     * @desc: assertResponse
     * @param \Psr\Http\Message\ResponseInterface $resp
     * @param $expected
     * @param $message
     * @return bool
     * @author Tinywan(ShaoBo Wan)
     */
    protected function assertResponse(\Psr\Http\Message\ResponseInterface $resp, $expected, $message)
    {
        $actual = $resp->getBody()->getContents();
        if ($expected !== $actual) {
            throw new NacosResponseException("$message, actual: {$actual}");
        }
        return true;
    }

    /**
     * removeConfig
     * 
     * @param string $dataId
     * @param string $group
     * @return string
     * @throws NacosRequestException
     */
    public function removeConfig(string $dataId, string $group = self::DEFAULT_GROUP)
    {
        $query = [
            'dataId' => $dataId,
            'group' => $group,
        ];

        if ($this->namespace) {
            $query['tenant'] = $this->namespace;
        }

        $resp = $this->request('DELETE', '/nacos/v1/cs/configs', ['query' => $query]);
        return $this->assertResponse($resp, 'true', "Nacos Client delete config fail");
    }

    /**
     * 监听配置
     * @param Config[] $configs
     * @param int $timeout 长轮训等待事件，默认 30 ，单位：秒
     * @return Config[]
     */
    public function listenConfig(array $configs, int $timeout = 30)
    {
        $configStringList = [];
        foreach ($configs as $cache) {
            $items = [$cache->dataId, $cache->group, $cache->contentMd5];
            if ($cache->namespace) {
                $items[] = $cache->namespace;
            }
            $configStringList[] = join(self::WORD_SEPARATOR, $items);
        }
        $configString = join(self::LINE_SEPARATOR, $configStringList) . self::LINE_SEPARATOR;

        $resp = $this->request('POST', '/nacos/v1/cs/configs/listener', [
            'timeout' => $timeout + $this->timeout,
            'headers' => ['Long-Pulling-Timeout' => $timeout * 1000],
            'form_params' => [
                'Listening-Configs' => $configString,
            ],
        ]);

        $respString = $resp->getBody()->getContents();
        if (!$respString) {
            return [];
        }

        $changed = [];
        $lines = explode(self::LINE_SEPARATOR, urldecode($respString));
        foreach ($lines as $line) {
            if (!empty($line)) {
                $parts = explode(self::WORD_SEPARATOR, $line);
                $c = new Config();
                if (count($parts) === 3) {
                    list($c->dataId, $c->group, $c->namespace) = $parts;
                } elseif (count($parts) === 2) {
                    list($c->dataId, $c->group) = $parts;
                } else {
                    continue;
                }
                $changed[] = $c;
            }
        }
        return $changed;
    }

    /**
     * 注册一个实例到服务
     * @param ServiceInstance $instance
     * @return boolean
     */
    public function createInstance(ServiceInstance $instance)
    {
        $instance->validate();
        $resp = $this->request('POST', '/nacos/v1/ns/instance', ['form_params' => $instance->toCreateParams()]);
        return $this->assertResponse($resp, 'ok', "Nacos Client create service instance fail");
    }

    /**
     * 删除服务下的一个实例
     * @param string $serviceName
     * @param string $ip
     * @param int $port
     * @param string|null $clusterName
     * @param string|null $namespaceId
     * @return boolean
     */
    public function deleteInstance(
        string $serviceName,
        string $ip,
        int $port,
        string $clusterName = null,
        string $namespaceId = null
    ) {
        $query = array_filter(compact('serviceName', 'ip', 'port', 'clusterName', 'namespaceId'));
        $resp = $this->request('DELETE', '/nacos/v1/ns/instance', ['query' => $query]);
        return $this->assertResponse($resp, 'ok', "Nacos Client delete service instance fail");
    }

    /**
     * @desc: 更新服务下的一个实例
     * @param ServiceInstance $instance
     * @return bool
     * @author Tinywan(ShaoBo Wan)
     */
    public function updateInstance(ServiceInstance $instance)
    {
        $instance->validate();
        $resp = $this->request('PUT', '/nacos/v1/ns/instance', ['form_params' => $instance->toUpdateParams()]);
        return $this->assertResponse($resp, 'ok', "Nacos Client update service instance fail");
    }

    /**
     * @desc: 方法描述
     * @param string $serviceName 服务名
     * @param string|null $namespaceId 命名空间ID
     * @param array $clusters 集群名称
     * @param bool $healthyOnly 是否只返回健康实例
     * @return ServiceInstanceList
     */
    public function getInstanceList(
        string $serviceName,
        string $namespaceId = null,
        array $clusters = [],
        bool $healthyOnly = false
    ) {
        $query = array_filter([
            'serviceName' => $serviceName,
            'namespaceId' => $namespaceId,
            'clusters' => join(',', $clusters),
            'healthyOnly' => $healthyOnly,
        ]);

        $resp = $this->request('GET', '/nacos/v1/ns/instance/list', [
            'http_errors' => false,
            'query' => $query,
        ]);

        $data = json_decode((string)$resp->getBody(), true);

        if (404 === $resp->getStatusCode()) {
            throw new NacosNamingNotFound(
                "service not found: $serviceName",
                404
            );
        }
        return new ServiceInstanceList($data);
    }

    /**
     * 查询一个服务下个某个实例详情
     *
     * @param string $serviceName      服务名
     * @param string $ip               实例IP
     * @param int $port                实例端口
     * @param string|null $namespaceId 命名空间 id
     * @param string|null $cluster     集群名称
     * @param bool $healthyOnly        是否只返回健康实例
     * @return ServiceInstance
     */
    public function getInstance(
        string $serviceName,
        string $ip,
        int $port,
        string $namespaceId = null,
        string $cluster = null,
        bool $healthyOnly = false
    ) {
        $query = array_filter(compact(
            'serviceName',
            'ip',
            'port',
            'namespaceId',
            'cluster',
            'healthyOnly'
        ));

        $resp = $this->request('GET', '/nacos/v1/ns/instance', ['query' => $query]);
        $data = json_decode((string)$resp->getBody(), true);
        $data['serviceName'] = $data['service'];

        return new ServiceInstance($data);
    }

    /**
     * 发送实例心跳
     * @param string $serviceName
     * @param BeatInfo $beat
     * @return BeatResult
     */
    public function sendInstanceBeat(string $serviceName, BeatInfo $beat)
    {
        $formParams = [
            'serviceName' => $serviceName,
            'namespaceId' => $this->namespace,
            'beat' => json_encode($beat)
        ];

        $resp = $this->request('PUT', '/nacos/v1/ns/instance/beat', ['form_params' => $formParams]);
        $array = json_decode((string) $resp->getBody(), true);

        $result = new BeatResult();
        $result->clientBeatInterval = $array['clientBeatInterval'];
        return $result;
    }
}
