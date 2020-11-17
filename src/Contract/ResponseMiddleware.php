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

namespace Fangx\HttpProxy\Contract;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ResponseMiddleware extends ProxyMiddleware
{
    public function __invoke(RequestInterface $request, array $options, callable $next)
    {
        /** @var PromiseInterface $promise */
        $promise = $next($request, $options);

        return $promise->then(function (ResponseInterface $response) {
            return $this->transfer($response);
        });
    }

    abstract public function transfer(ResponseInterface $response): ResponseInterface;
}
