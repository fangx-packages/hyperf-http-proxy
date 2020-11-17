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

use Fangx\HttpProxy\Contract\ResponseMiddleware;
use Psr\Http\Message\ResponseInterface;

class KeepResponseHeaders extends ResponseMiddleware
{
    protected $headers = [];

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function transfer(ResponseInterface $response): ResponseInterface
    {
        foreach ($response->getHeaders() as $header => $value) {
            if (! in_array($header, $this->headers)) {
                $response = $response->withoutHeader($header);
            }
        }

        return $response;
    }
}
