## Fangx's Packages

### Install

Via Composer

```
composer require fangx/http-proxy
```

### Config

```
php bin/hyperf.php vendor:publish fangx/http-proxy
```

Or create config/autoload/http-proxy.php

```php
<?php

// 默认的 headers / options / middlewares 都可以在每个 proxy 里单独配置
return [
    // 代理时, 默认保留的请求/响应头信息
    'headers' => [
        'request' => [
            'content-type',
            'user-agent',
            'authorization',
            'accept',
        ],
        'response' => [
            'content-type',
            'content-disposition',
        ],
    ],

    // 默认的 Guzzle 配置
    'options' => [
        'timeout' => 10,
    ],

    // 默认中间件
    'middlewares' => [
        // \Fangx\HttpProxy\Middleware\LoggerMiddleware::class
    ],

    // Proxy urls
    'proxy' => [
        'default' => [
            'url' => 'http://127.0.0.1:9502/open-api/',
        ],
        'other' => [
            'url' => 'http://127.0.0.1:9503/open-api/',
        ],
    ],
];

```

### Usage

如下所示, 所有请求 `/proxy/*` 都会转发到 `http://127.0.0.1:9502/open-api/*`, 所有请求 `/proxy-other/*` 都会转发到 `http://127.0.0.1:9503/open-api/*`

> 路由

```php
Router::addRoute(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH'], '/proxy/{uri:.*}', [IndexController::class, 'proxy']);
Router::addRoute(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH'], '/proxy-other/{uri:.*}', [IndexController::class, 'proxyOther']);
```

> 控制器

```php

use Hyperf\HttpServer\Contract\RequestInterface;
use Fangx\HttpProxy\ProxyFactory;

class IndexController
{
    public function proxy(RequestInterface $request, ProxyFactory $factory)
    {
        return $factory->make()->proxy($request, $request->route('uri'));
    }
    
    public function proxyOther(RequestInterface $request, ProxyFactory $factory)
    {
        return $factory->make('other')->proxy($request, $request->route('uri'));
    }
}
```
