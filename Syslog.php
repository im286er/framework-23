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

namespace Leevel\Log;

use Monolog\Handler\SyslogHandler;

/**
 * log.syslog.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.09.01
 *
 * @version 1.0
 */
class Syslog extends Monolog
{
    /**
     * 配置.
     *
     * @var array
     */
    protected $option = [
        'channel' => 'query',
    ];

    /**
     * 构造函数.
     *
     * @param array $option
     */
    public function __construct(array $option = [])
    {
        parent::__construct($option);

        $this->makeSyslogHandler();
    }

    /**
     * 初始化系统 handler.
     */
    protected function makeSyslogHandler()
    {
        $this->syslog();
    }

    /**
     * 注册系统 handler.
     *
     * @param string $name
     * @param string $level
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function syslog($name = 'log', $level = ILog::DEBUG)
    {
        $handler = new SyslogHandler($name, LOG_USER, $level);

        return $this->monolog->pushHandler($handler);
    }
}
