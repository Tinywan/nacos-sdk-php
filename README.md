# Nacos-sdk-php

A PHP implementation of Nacos OpenAPI. [Open API Guide](https://nacos.io/en-us/docs/open-api.html)

## Dependencies

- PHP >= 7.2.5
- CURL Extension

## Nacos version

Nacos 1.3.0 ~ 1.4.2

## Installation

```php
composer require tinywan/nacos-sdk-php
```

## Getting Started

### crontab

```php
// nacos.php
\nacos\Client::init(
    "http://127.0.0.1:8848/",
    "dev",
    "redis.php",
    "DEFAULT_GROUP",
    "4b5ca7ac-3e2a-4456-a15f-f04738345699"
)->runOnce();
```

> cron `* * * * * bin/php path/to/nacos.php`

### listener

```php
// scheduler.php
\nacos\Client::init(
    "http://127.0.0.1:8848/",
    "dev",
    "database.php",
    "DEFAULT_GROUP",
    "4b5ca7ac-3e2a-4456-a15f-f04738345699"
)->listener();
```

> daemon `nohup bin/php path/to/scheduler.php 1>> /dev/null 2>&1`

### modify config directory 

The working directory saved in the configuration file can be modified by the following command
```
NacosConfig::setSnapshotPath(__DIR__);
```
## Docker

`http://127.0.0.1:8848/` replace `http://192.168.2.108:8848/`

> ipconfig or ifconfig get `192.168.2.108`

## Other

```
psr4 Error: Class 'nacos\Client' not found
```

> need `composer dump-autoload`
