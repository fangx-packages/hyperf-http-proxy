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

class KeepRequestHeaders extends RequestMiddleware
{
    /**
     * @var array
     */
    protected $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function transfer(RequestInterface $request): RequestInterface
    {
        foreach ($request->getHeaders() as $header => $value) {
            if (! in_array($header, $this->headers)) {
                $request = $request->withoutHeader($header);
            }
        }

        return $request;
    }
}
