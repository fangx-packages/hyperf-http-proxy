<?php

declare(strict_types=1);
/**
 * Fangx's Package.
 *
 * @link     https://nfangxu.com
 * @document https://pkg.nfangxu.com
 * @contact  nfangxu@gmail.com
 * @license  https://pkg.nfangxu.com/license
 */
namespace Fangx\HttpProxy;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                    ],
                ],
            ],
            'publish' => [
//                [
//                    'id' => 'config',
//                    'description' => 'The config for hyperf component.',
//                    'source' => __DIR__ . '/../publish/config.php',
//                    'destination' => BASE_PATH . '/config/autoload/package-name.php',
//                ],
            ],
        ];
    }
}
