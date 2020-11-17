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

namespace Fangx\HttpProxy\Middleware;

use Fangx\HttpProxy\Contract\RequestMiddleware;
use Psr\Http\Message\RequestInterface;

class ProxyUri extends RequestMiddleware
{
    /**
     * @var string
     */
    protected $scheme = 'http';

    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var int
     */
    protected $port = 80;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $uri = '';

    public function __construct(string $scheme, string $host, int $port, string $prefix, string $uri)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->prefix = trim($prefix, '/');
        $this->uri = $uri;
    }

    public function transfer(RequestInterface $request): RequestInterface
    {
        return $request->withUri($request->getUri()
            ->withScheme($this->scheme)
            ->withHost($this->host)
            ->withPort($this->port)
            ->withPath(sprintf('/%s/%s', $this->prefix, ltrim($this->uri ?: $request->getUri()->getPath(), '/'))));
    }
}
