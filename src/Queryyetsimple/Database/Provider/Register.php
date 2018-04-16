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
namespace Leevel\Database\Provider;

use Leevel\{
    Di\Provider,
    Database\Manager
};

/**
 * database 服务提供者
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.05.12
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
        $this->databases();
        $this->database();
    }

    /**
     * 载入命令包
     *
     * @return void
     */
    public function bootstrap()
    {
        $this->loadCommand('Leevel\Database\Console');
    }

    /**
     * 可用服务提供者
     *
     * @return array
     */
    public static function providers()
    {
        return [
            'databases' => [
                'Leevel\Database\Manager'
            ],
            'database' => [
                'Leevel\Database\Database',
                'Leevel\Database\IDatabase'
            ]
        ];
    }

    /**
     * 注册 databases 服务
     *
     * @return void
     */
    protected function databases()
    {
        $this->singleton('databases', function ($project) {
            return new Manager($project);
        });
    }

    /**
     * 注册 database 服务
     *
     * @return void
     */
    protected function database()
    {
        $this->singleton('database', function ($project) {
            return $project['databases']->connect();
        });
    }
}
