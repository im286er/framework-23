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

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

/**
 * connect 驱动抽象类.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.09.01
 *
 * @version 1.0
 */
abstract class Connect extends Connect implements IConnect
{
    /**
     * Monolog.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;

    /**
     * 配置.
     *
     * @var array
     */
    protected $option = [
        'channel' => 'query',
    ];

    /**
     * Monolog 支持日志级别.
     *
     * @var array
     */
    protected $supportLevel = [
        ILog::DEBUG     => Logger::DEBUG,
        ILog::INFO      => Logger::INFO,
        ILog::NOTICE    => Logger::NOTICE,
        ILog::WARNING   => Logger::WARNING,
        ILog::ERROR     => Logger::ERROR,
        ILog::CRITICAL  => Logger::CRITICAL,
        ILog::ALERT     => Logger::ALERT,
        ILog::EMERGENCY => Logger::EMERGENCY,
    ];

    /**
     * 构造函数.
     *
     * @param array $option
     */
    public function __construct(array $option = [])
    {
        $this->option = array_merge($this->option, $option);

        $this->monolog = new Logger($this->option['channel']);
    }

    /**
     * 设置配置.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption(string $name, $value)
    {
        $this->option[$name] = $value;

        return $this;
    }

    /**
     * 日志写入接口.
     *
     * @param array $data
     */
    public function flush(array $data)
    {
        $level = array_keys($this->supportLevel);

        foreach ($data as $item) {
            if (!in_array($item[0], $level, true)) {
                $item[0] = ILog::DEBUG;
            }

            $this->monolog->{$item[0]}($item[1], $item[2]);
        }
    }

    /**
     * 默认格式化.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter(null, null, true, true);
    }

    /**
     * 获取 Monolog 级别
     * 不支持级别归并到 DEBUG.
     *
     * @param string $level
     *
     * @return int
     */
    protected function parseMonologLevel($level)
    {
        if (isset($this->supportLevel[$level])) {
            return $this->supportLevel[$level];
        }

        return $this->supportLevel[ILog::DEBUG];
    }
}
