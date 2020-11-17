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

use GuzzleHttp\Psr7\Utils;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ProxyMiddleware
{
    abstract public function __invoke(RequestInterface $request, array $options, callable $next);

    protected function getQueryParams(RequestInterface $request): array
    {
        parse_str($request->getUri()->getQuery(), $data);
        return $data ?: [];
    }

    protected function setQueryParams(RequestInterface $request, array $params): RequestInterface
    {
        return $request->withUri($request->getUri()->withQuery(http_build_query($params)));
    }

    protected function getBodyParams(RequestInterface $request): array
    {
        $params = $request->getBody()->getContents();

        $data = json_decode($params, true);

        if (! $data) {
            parse_str($params, $data);
        }

        return $data ?: [];
    }

    protected function setBodyParams(RequestInterface $request, array $params): RequestInterface
    {
        $body = $this->isJsonBody($request) ? Json::encode($params) : http_build_query($params);

        return $request->withBody(Utils::streamFor($body));
    }

    protected function isJsonBody(RequestInterface $request): bool
    {
        return Str::contains($request->getHeaderLine('content-type'), [
            'application/json',
        ]);
    }

    protected function isUploadRequest(RequestInterface $request): bool
    {
        return Str::contains($request->getHeaderLine('content-type'), [
            'multipart/form-data',
        ]);
    }

    protected function isDownloadResponse(ResponseInterface $response): bool
    {
        return (bool)$response->getHeaderLine('content-disposition');
    }
}
