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

namespace Leevel\Bootstrap\Console;

use Leevel\Console\Command;
use Leevel\Kernel\IProject;

/**
 * 自动加载缓存清理.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.09.04
 *
 * @version 1.0
 * @codeCoverageIgnore
 */
class AutoloadClear extends Command
{
    /**
     * 命令名字.
     *
     * @var string
     */
    protected $name = 'autoload:clear';

    /**
     * 命令行描述.
     *
     * @var string
     */
    protected $description = 'Clear cache of autoload.';

    /**
     * 响应命令.
     *
     * @param \Leevel\Kernel\IProject $project
     */
    public function handle(IProject $project)
    {
        $this->line('Start to clear cache autoload.');

        $cachePath = $project->runtimePath('autoload.php');

        $this->clearCache($cachePath);

        $this->info(sprintf('Autoload file %s cache clear successed.', $cachePath));
    }

    /**
     * 删除缓存.
     *
     * @param string $cachePath
     */
    protected function clearCache(string $cachePath)
    {
        if (!is_file($cachePath)) {
            $this->warn('Autoload cache files have been cleaned up.');

            return;
        }

        unlink($cachePath);
    }

    /**
     * 命令参数.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * 命令配置.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }
}
