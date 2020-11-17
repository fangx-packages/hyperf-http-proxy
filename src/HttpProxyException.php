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

class HttpProxyException extends \Exception
{
    protected $exception;

    public function __construct(\Throwable $exception)
    {
        parent::__construct('[Http Proxy Error]: ' . $exception->getMessage(), 500, $exception);
        $this->exception = $exception;
    }

    public function getException()
    {
        return $this->exception;
    }
}
