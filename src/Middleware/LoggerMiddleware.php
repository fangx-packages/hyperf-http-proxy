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

use Fangx\HttpProxy\Contract\ProxyMiddleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoggerMiddleware extends ProxyMiddleware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('http-proxy');
    }

    public function __invoke(RequestInterface $request, array $options, callable $next)
    {
        if ($this->isUploadRequest($request)) {
            $body = 'upload files.';
        } else {
            $request = $this->setBodyParams($request, $data = $this->getBodyParams($request));
            $body = json_encode($data);
        }

        $this->logger->info('Proxy request: ' . $request->getUri()->getPath(), [
            'proxy_request_body' => $body,
        ]);

        /** @var PromiseInterface $promise */
        $promise = $next($request, $options);

        return $promise->then(function (ResponseInterface $response) {
            if ($this->isDownloadResponse($response)) {
                $body = 'download file.';
            } else {
                $response = $response->withBody(Utils::streamFor($body = $response->getBody()->getContents()));
            }

            $this->logger->info('Proxy response: ' . $response->getStatusCode(), [
                'proxy_response_body' => $body,
            ]);

            return $response;
        });
    }
}
