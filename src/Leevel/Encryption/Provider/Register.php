<?php

declare(strict_types=1);

/*
 * This file is part of the ************************ package.
 * _____________                           _______________
 *  ______/     \__  _____  ____  ______  / /_  _________
 *   ____/ __   / / / / _ \/ __`\/ / __ \/ __ \/ __ \___
 *    __/ / /  / /_/ /  __/ /  \  / /_/ / / / / /_/ /__
 *      \_\ \_/\____/\___/_/   / / .___/_/ /_/ .___/
 *         \_\                /_/_/         /_/
 *
 * The PHP Framework For Code Poem As Free As Wind. <Query Yet Simple>
 * (c) 2010-2018 http://queryphp.com All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Leevel\Encryption\Provider;

use Leevel\Di\IContainer;
use Leevel\Di\Provider;
use Leevel\Encryption\Encryption;

/**
 * encryption 服务提供者.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.06.03
 *
 * @version 1.0
 */
class Register extends Provider
{
    /**
     * 注册服务
     */
    public function register()
    {
        $this->container->singleton('encryption', function (IContainer $container) {
            return new Encryption($container['option']['auth_key'], $container['option']['auth_cipher']);
        });
    }

    /**
     * 可用服务提供者.
     *
     * @return array
     */
    public static function providers(): array
    {
        return [
            'encryption' => [
                'Leevel\\Encryption\\Encryption',
                'Leevel\\Encryption\\IEncryption',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function isDeferred(): bool
    {
        return true;
    }
}
