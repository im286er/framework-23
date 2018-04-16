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
namespace Leevel\Mail\Provider;

use Leevel\{
    Di\Provider,
    Mail\Manager
};

/**
 * mail 服务提供者
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.08.26
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
        $this->mails();
        $this->mail();
    }

    /**
     * 可用服务提供者
     *
     * @return array
     */
    public static function providers()
    {
        return [
            'mails' => [
                'Leevel\Mail\Manager'
            ],
            'mail' => [
                'Leevel\Mail\Mail',
                'Leevel\Mail\IMail'
            ]
        ];
    }

    /**
     * 注册 mails 服务
     *
     * @return void
     */
    protected function mails()
    {
        $this->singleton('mails', function ($project) {
            return new Manager($project);
        });
    }

    /**
     * 注册 mail 服务
     *
     * @return void
     */
    protected function mail()
    {
        $this->singleton('mail', function ($project) {
            return $project['mails']->connect();
        });
    }
}
