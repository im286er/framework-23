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

namespace Leevel\Cache\Provider;

use Leevel\Cache\Load;
use Leevel\Cache\Manager;
use Leevel\Di\IContainer;
use Leevel\Di\Provider;

/**
 * cache 服务提供者.
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
        $this->caches();
        $this->cache();
        $this->cacheLoad();
    }

    /**
     * 可用服务提供者.
     *
     * @return array
     */
    public static function providers(): array
    {
        return [
            'caches' => [
                'Leevel\\Cache\\Manager',
            ],
            'cache' => [
                'Leevel\\Cache\\Cache',
                'Leevel\\Cache\\ICache',
            ],
            'cache.load' => [
                'Leevel\\Cache\\Load',
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

    /**
     * 注册 caches 服务
     */
    protected function caches()
    {
        $this->container->singleton('caches', function (IContainer $container) {
            return new Manager($container);
        });
    }

    /**
     * 注册 cache 服务
     */
    protected function cache()
    {
        $this->container->singleton('cache', function (IContainer $container) {
            return $container['caches']->connect();
        });
    }

    /**
     * 注册 cache.load 服务
     */
    protected function cacheLoad()
    {
        $this->container->singleton('cache.load', function (IContainer $container) {
            return new Load($container, $container['cache']);
        });
    }
}
