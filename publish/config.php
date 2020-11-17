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

return [
    // Default Keeps headers
    'headers' => [
        'request' => [
            'content-type',
            'user-agent',
            'authorization',
            'accept',
        ],
        'response' => [
            'content-type',
            'content-disposition',
        ],
    ],

    // Default guzzle options
    'options' => [
        'timeout' => 10,
    ],

    // Default middlewares
    'middlewares' => [
        // \Fangx\HttpProxy\Middleware\LoggerMiddleware::class
    ],

    // Proxy urls
    'proxy' => [
        'default' => [
            'url' => 'http://127.0.0.1:9502/open-api/',
            //'headers' => [
            //    'request' => [
            //        'content-type',
            //        'user-agent',
            //        'authorization',
            //        'accept',
            //    ],
            //    'response' => [
            //        'content-type',
            //        'content-disposition',
            //    ],
            //],
        ],
    ],
];
