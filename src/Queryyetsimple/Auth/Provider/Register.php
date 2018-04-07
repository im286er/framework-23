<?php
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
namespace Leevel\Auth\Provider;

use Leevel\{
    Di\Provider,
    Auth\Manager
};

/**
 * auth 服务提供者
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.09.08
 * @version 1.0
 */
class Register extends Provider
{

    /**
     * 注册服务
     *
     * @return void
     */
    public function register()
    {
        $this->auths();
        $this->auth();
    }

    /**
     * 可用服务提供者
     *
     * @return array
     */
    public static function providers()
    {
        return [
            'auths' => [
                'Leevel\Auth\Manager',
                'Qys\Auth\Manager'
            ],
            'auth' => [
                'Leevel\Auth\Auth',
                'Leevel\Auth\IAuth',
                'Qys\Auth\Auth',
                'Qys\Auth\IAuth'
            ]
        ];
    }

    /**
     * 注册 auths 服务
     *
     * @return void
     */
    protected function auths()
    {
        $this->singleton('auths', function ($project) {
            return new Manager($project);
        });
    }

    /**
     * 注册 auth 服务
     *
     * @return void
     */
    protected function auth()
    {
        $this->singleton('auth', function ($project) {
            return $project['auths']->connect();
        });
    }
}