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

namespace Leevel\Page;

/**
 * 明确总数分页处理.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.07.14
 *
 * @version 1.0
 */
class PageWithTotal extends Page
{
    /**
     * 构造函数.
     *
     * @param int   $perPage
     * @param int   $totalRecord
     * @param array $option
     */
    public function __construct(int $perPage, int $totalRecord, array $option = [])
    {
        $this->perPage = $perPage;
        $this->totalRecord = $totalRecord;

        $this->option = array_merge($this->option, $option);
    }
}
