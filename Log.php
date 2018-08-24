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

/**
 * 日志仓储.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.03.03
 *
 * @version 1.0
 */
class Log implements ILog
{
    /**
     * 存储连接对象
     *
     * @var \Leevel\Log\IConnect
     */
    protected $connect;

    /**
     * 当前记录的日志信息.
     *
     * @var array
     */
    protected $logs = [];

    /**
     * 日志过滤器.
     *
     * @var callable
     */
    protected $filter;

    /**
     * 日志处理器.
     *
     * @var callable
     */
    protected $processor;

    /**
     * 配置.
     *
     * @var array
     */
    protected $option = [
        'level'   => [
            self::DEBUG,
            self::INFO,
            self::NOTICE,
            self::WARNING,
            self::ERROR,
            self::CRITICAL,
            self::ALERT,
            self::EMERGENCY,
        ],
        'time_format' => '[Y-m-d H:i]',
    ];

    /**
     * 构造函数.
     *
     * @param \Leevel\Log\IConnect $connect
     * @param array                $option
     */
    public function __construct(IConnect $connect, array $option = [])
    {
        $this->connect = $connect;

        $this->option = array_merge($this->option, $option);
    }

    /**
     * call.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->connect->{$method}(...$args);
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
     * 系统无法使用.
     *
     * @param string $message
     * @param array  $context
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(static::EMERGENCY, $message, $context);
    }

    /**
     * 必须立即采取行动.
     *
     * 比如: 整个网站宕机，数据库不可用等等.
     * 这种错误应该通过短信通知你.
     *
     * @param string $message
     * @param array  $context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(static::ALERT, $message, $context);
    }

    /**
     * 临界条件.
     *
     * 比如: 应用程序组件不可用，意外异常.
     *
     * @param string $message
     * @param array  $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(static::CRITICAL, $message, $context);
    }

    /**
     * 运行时错误，不需要立即处理.
     * 但是需要被记录和监控.
     *
     * @param string $message
     * @param array  $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(static::ERROR, $message, $context);
    }

    /**
     * 非错误的异常事件.
     *
     * 比如: 弃用的 API 接口, API 使用不足, 不良事物.
     * 它们不一定是错误的.
     *
     * @param string $message
     * @param array  $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(static::WARNING, $message, $context);
    }

    /**
     * 正常重要事件.
     *
     * @param string $message
     * @param array  $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(static::NOTICE, $message, $context);
    }

    /**
     * 想记录的日志.
     *
     * 比如: 用户日志, SQL 日志.
     *
     * @param string $message
     * @param array  $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(static::INFO, $message, $context);
    }

    /**
     * 调试信息.
     *
     * @param string $message
     * @param array  $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(static::DEBUG, $message, $context);
    }

    /**
     * 记录特定级别的日志信息.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // 只记录系统允许的日志级别
        if (!in_array($level, $this->option['level'], true)) {
            return;
        }

        $message = date($this->option['time_format']).$message;

        $data = [
            $level,
            $message,
            $context,
        ];

        // 执行过滤器
        if (null !== $this->filter && false === call_user_func_array($this->filter, $data)) {
            return;
        }

        // 记录到内存方便后期调用
        if (!isset($this->logs[$level])) {
            $this->logs[$level] = [];
        }

        $this->logs[$level][] = $data;
    }

    /**
     * 保存日志信息.
     */
    public function flush()
    {
        if (!$this->logs) {
            return;
        }

        foreach ($this->logs as $data) {
            $this->saveStore($data);
        }

        $this->clear();
    }

    /**
     * 清理日志记录.
     *
     * @param string $level
     *
     * @return int
     */
    public function clear(?string $level = null): int
    {
        if ($level && isset($this->logs[$level])) {
            $count = count($this->logs[$level]);
            $this->logs[$level] = [];
        } else {
            $count = count($this->logs);
            $this->logs = [];
        }

        return $count;
    }

    /**
     * 获取日志记录.
     *
     * @param string $level
     *
     * @return array
     */
    public function all(?string $level = null): array
    {
        if ($level && isset($this->logs[$level])) {
            return $this->logs[$level];
        }

        return $this->logs;
    }

    /**
     * 获取日志记录数量.
     *
     * @param string $level
     *
     * @return int
     */
    public function count(?string $level = null): int
    {
        if ($level && isset($this->logs[$level])) {
            return count($this->logs[$level]);
        }

        return count($this->logs);
    }

    /**
     * 注册日志过滤器.
     *
     * @param callable $filter
     */
    public function registerFilter(callable $filter)
    {
        $this->filter = $filter;
    }

    /**
     * 注册日志处理器.
     *
     * @param callable $processor
     */
    public function registerProcessor(callable $processor)
    {
        $this->processor = $processor;
    }

    /**
     * 存储日志.
     *
     * @param array $data
     */
    protected function saveStore(array $data)
    {
        // 执行处理器
        if (null !== $this->processor) {
            call_user_func_array($this->processor, $data);
        }

        $this->connect->flush($data);
    }
}
