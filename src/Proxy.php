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
use Fangx\HttpProxy\Middleware\KeepRequestHeaders;
use Fangx\HttpProxy\Middleware\KeepResponseHeaders;
use Fangx\HttpProxy\Middleware\ProxyUri;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Proxy
{
    /**
     * @var string[]
     */
    protected $keepRequestHeaders = [];

    /**
     * @var string[]
     */
    protected $keepResponseHeaders = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ProxyMiddleware[]
     */
    protected $middlewares = [];

    /**
     * @var array
     */
    protected $config;

    public function __construct(Client $client, string $url, array $keepRequestHeaders = ['content-type'], array $keepResponseHeaders = ['content-type', 'content-disposition'], array $middlewares = [])
    {
        $this->client = $client;
        $this->keepRequestHeaders = $keepRequestHeaders;
        $this->keepResponseHeaders = $keepResponseHeaders;
        $this->middlewares = $middlewares;
        $this->config = (array)parse_url($url);
    }

    public function proxy(RequestInterface $request, string $path = '')
    {
        try {
            $response = $this
                // transfer uri
                ->withAddMiddleware(new ProxyUri(
                    $this->config['scheme'] ?? 'http',
                    $this->config['host'] ?? '',
                    $this->config['port'] ?? 80,
                    $this->config['path'] ?? '',
                    $path
                ))
                // remove request headers
                ->withAddMiddleware(new KeepRequestHeaders($this->keepRequestHeaders))
                // remove response headers
                ->withAddMiddleware(new KeepResponseHeaders($this->keepResponseHeaders))
                ->client()
                ->send($this->toGzRequest($request));
        } catch (Throwable $exception) {
            throw new HttpProxyException($exception);
        }

        return $this->toSwResponse($response);
    }

    public function withAddMiddleware(ProxyMiddleware $middleware): self
    {
        $new = clone $this;
        $new->middlewares[] = $middleware;
        return $new;
    }

    protected function toGzRequest(RequestInterface $request)
    {
        return $request->withBody(Utils::streamFor($request->getBody()->getContents()));
    }

    protected function toSwResponse(ResponseInterface $psrResponse)
    {
        return $psrResponse->withBody(new SwooleStream($psrResponse->getBody()->getContents()));
    }

    protected function client()
    {
        /** @var \GuzzleHttp\HandlerStack $stack */
        $stack = $this->client->getConfig('handler');

        foreach ($this->middlewares as $middleware) {
            if (! $middleware instanceof ProxyMiddleware) {
                continue;
            }

            $stack->push(function ($handler) use ($middleware) {
                return function ($request, $options) use ($handler, $middleware) {
                    return $middleware($request, $options, $handler);
                };
            });
        }

        return $this->client;
    }
}
