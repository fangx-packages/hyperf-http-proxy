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
            $response = $this->client()->send($this->toRequest($request, $path));
        } catch (Throwable $exception) {
            throw new HttpProxyException($exception);
        }

        return $this->toResponse($response);
    }

    public function withAddMiddleware(ProxyMiddleware $middleware): self
    {
        $new = clone $this;
        $new->middlewares[] = $middleware;
        return $new;
    }

    protected function toRequest(RequestInterface $request, string $path = '')
    {
        $path = $path ?: $request->getUri()->getPath();

        $uri = $request->getUri()
            ->withScheme($this->config['scheme'] ?? 'http')
            ->withHost($this->config['host'] ?? '127.0.0.1')
            ->withPort($this->config['port'] ?? 80)
            ->withPath(sprintf('/%s/%s', trim($this->config['path'] ?? '', '/'), ltrim($path, '/')));

        foreach ($request->getHeaders() as $header => $value) {
            if (! in_array($header, $this->keepRequestHeaders)) {
                $request = $request->withoutHeader($header);
            }
        }

        return $request->withUri($uri)->withBody(Utils::streamFor($request->getBody()->getContents()));
    }

    /**
     * @return ResponseInterface
     */
    protected function toResponse(ResponseInterface $psrResponse)
    {
        foreach ($psrResponse->getHeaders() as $header => $value) {
            if (! in_array($header, $this->keepResponseHeaders)) {
                $psrResponse = $psrResponse->withoutHeader($header);
                continue;
            }
        }

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
