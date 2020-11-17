<?php

declare(strict_types=1);

/**
 * Fangx's Packages
 *
 * @link     https://nfangxu.com
 * @document https://pkg.nfangxu.com
 * @contact  nfangxu@gmail.com
 * @author   nfangxu
 * @license  https://pkg.nfangxu.com/license
 */

namespace Fangx\HttpProxy;

use Fangx\HttpProxy\Contract\ProxyMiddleware;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class ProxyFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ClientFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $config = [];

    public function __construct(ContainerInterface $container, ClientFactory $factory, ConfigInterface $config)
    {
        $this->container = $container;
        $this->factory = $factory;
        $this->config = $config->get('http-proxy');
    }

    public function make(string $proxy = 'default')
    {
        if (! isset($this->config['proxy'][$proxy])) {
            throw new \RuntimeException(sprintf('Proxy [%s] config is not defined!', $proxy));
        }

        return new Proxy(
            $this->factory->create((array)$this->getConfig($proxy, 'options')),
            (string)$this->getConfig($proxy, 'url'),
            (array)$this->getConfig($proxy, 'headers.request'),
            (array)$this->getConfig($proxy, 'headers.response'),
            (array)$this->getDefaultMiddlewares($proxy)
        );
    }

    /**
     * @return ProxyMiddleware[]
     */
    private function getDefaultMiddlewares(string $proxy)
    {
        $middlewares = [];

        foreach ($this->getConfig($proxy, 'middlewares') as $middleware) {
            if (! is_object($middleware)) {
                $middleware = clone $this->container->get($middleware);
            }

            if (! $middleware instanceof ProxyMiddleware) {
                continue;
            }

            array_push($middlewares, $middleware);
        }

        return $middlewares;
    }

    private function getConfig(string $proxy, string $key)
    {
        return Arr::get($this->config, sprintf('proxy.%s.%s', $proxy, $key))
            ?: Arr::get($this->config, sprintf('proxy.%s', $key));
    }
}
