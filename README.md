# nacos-sdk-php

A PHP implementation of Nacos OpenAPI.

see: https://nacos.io/zh-cn/docs/open-API.html

## Dependencies

* PHP >= 7.0
* CURL Extension

## Nacos version

Nacos 1.3.0 ~ 1.4.0

## Installation

```php
composer require tinywan/nacos
```

## Getting Started

cron

```php
// nacos.php

Nacos::init(
    "http://127.0.0.1:8848/",
    "dev",
    "redis.php",
    "DEFAULT_GROUP",
    "4b5ca7ac-3e2a-4456-a15f-f04738345699"
)->runOnce();
```
> cron `* * * * * bin/php path/to/nacos.php`

listener

```php
Nacos::init(
    "http://127.0.0.1:8848/",
    "dev",
    "LARAVEL",
    "DEFAULT_GROUP",
    "4b5ca7ac-3e2a-4456-a15f-f04738345699"
)->listener();
```
