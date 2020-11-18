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

use GuzzleHttp\Exception\BadResponseException as GzBadResponseException;
use GuzzleHttp\Exception\GuzzleException as GzException;
use GuzzleHttp\Exception\RequestException as GzRequestException;
use GuzzleHttp\Exception\TransferException as GzTransferException;

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

    public function isGzException(): bool
    {
        return $this->exception instanceof GzException;
    }

    public function isGzTransferException(): bool
    {
        return $this->exception instanceof GzTransferException;
    }

    public function isGzRequestException(): bool
    {
        return $this->exception instanceof GzRequestException;
    }

    public function isGzBadResponseException(): bool
    {
        return $this->exception instanceof GzBadResponseException;
    }
}
