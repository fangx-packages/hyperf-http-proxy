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

use Psr\Http\Message\RequestInterface;

abstract class RequestMiddleware extends ProxyMiddleware
{
    public function __invoke(RequestInterface $request, array $options, callable $next)
    {
        return $next($this->transfer($request), $options);
    }

    abstract public function transfer(RequestInterface $request): RequestInterface;
}
