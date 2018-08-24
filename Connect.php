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

use InvalidArgumentException;

/**
 * aconnect 驱动抽象类.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.08.26
 *
 * @version 1.0
 */
abstract class Connect
{
    /**
     * 构造函数.
     *
     * @param array $option
     */
    public function __construct(array $option = [])
    {
        $this->option = array_merge($this->option, $option);
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
     * 验证日志文件大小.
     *
     * @param string $filePath
     */
    protected function checkSize(string $filePath): void
    {
        $dirname = dirname($filePath);

        if (!is_file($filePath)) {
            if (!is_dir($dirname)) {
                if (is_dir(dirname($dirname)) && !is_writable(dirname($dirname))) {
                    throw new InvalidArgumentException(
                        sprintf('Unable to create the %s directory.', $dirname)
                    );
                }

                mkdir($dirname, 0777, true);
            }

            if (!is_writable($dirname)) {
                throw new InvalidArgumentException(
                    sprintf('Dir %s is not writeable.', $dirname)
                );
            }
        }

        // 清理文件状态缓存 http://php.net/manual/zh/function.clearstatcache.php
        clearstatcache();

        if (is_file($filePath) &&
            floor($this->option['size']) <= filesize($filePath)) {
            rename($filePath,
                $dirname.'/'.basename($filePath, '.log').'_'.
                (time() - filemtime($filePath)).'.log');
        }
    }

    /**
     * 获取日志路径.
     *
     * @param string $level
     *
     * @return string
     */
    protected function getPath(string $level = ''): string
    {
        if (!$this->option['path']) {
            throw new InvalidArgumentException(
                'Path for log has not set.'
            );
        }

        $filePath = $this->option['path'].'/'.
            ($level ? $level.'/' : '').
            date($this->option['name']).'.log';

        return $filePath;
    }
}
