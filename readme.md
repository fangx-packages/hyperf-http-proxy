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

#### Change Request

> 定义

```php
use Fangx\HttpProxy\Contract\RequestMiddleware as ProxyMiddleware;
use Psr\Http\Message\RequestInterface;

class AddAuthorization extends ProxyMiddleware
{
    public function transfer(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('authorization', 'Bearer 1234567890');
    }
}
```

> 使用

```php
use Hyperf\HttpServer\Contract\RequestInterface;
use Fangx\HttpProxy\ProxyFactory;

class IndexController
{
    public function proxy(RequestInterface $request, ProxyFactory $factory)
    {
        return $factory->make()
                    ->withAddMiddleware(new AddAuthorization())
                    ->proxy($request, $request->route('uri'));
    }
}
```

#### Change Response


> 定义

```php
use Fangx\HttpProxy\Contract\ResponseMiddleware as ProxyMiddleware;
use Psr\Http\Message\ResponseInterface;

class ConvertContentType extends ProxyMiddleware
{
    public function transfer(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('content-type', 'text/html');
    }
}
```

> 使用

```php
use Hyperf\HttpServer\Contract\RequestInterface;
use Fangx\HttpProxy\ProxyFactory;

class IndexController
{
    public function proxy(RequestInterface $request, ProxyFactory $factory)
    {
        return $factory->make()
                    ->withAddMiddleware(new ConvertContentType())
                    ->proxy($request, $request->route('uri'));
    }
}
```

#### 直接定义 Middleware

> \Fangx\HttpProxy\Middleware\LoggerMiddleware::class

```php
use Fangx\HttpProxy\Contract\ProxyMiddleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoggerMiddleware extends ProxyMiddleware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('http-proxy');
    }

    public function __invoke(RequestInterface $request, array $options, callable $next)
    {
        if ($this->isUploadRequest($request)) {
            $body = 'upload files.';
        } else {
            $request = $this->setBodyParams($request, $data = $this->getBodyParams($request));
            $body = json_encode($data);
        }

        $this->logger->info('Proxy request: ' . $request->getUri()->getPath(), [
            'proxy_request_body' => $body,
        ]);

        /** @var PromiseInterface $promise */
        $promise = $next($request, $options);

        return $promise->then(function (ResponseInterface $response) {
            if ($this->isDownloadResponse($response)) {
                $body = 'download file.';
            } else {
                $response = $response->withBody(Utils::streamFor($body = $response->getBody()->getContents()));
            }

            $this->logger->info('Proxy response: ' . $response->getStatusCode(), [
                'proxy_response_body' => $body,
            ]);

            return $response;
        });
    }
}

```
