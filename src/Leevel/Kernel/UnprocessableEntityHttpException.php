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

namespace Leevel\Kernel;

use Exception;

/**
 * 无法处理的实体
 * 请求格式正确，但是由于含有语义错误，无法响应: 422.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.04.29
 *
 * @version 1.0
 */
class UnprocessableEntityHttpException extends HttpException
{
    /**
     * 构造函数.
     *
     * @param null|string $message
     * @param int         $code
     * @param \Exception  $previous
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct(422, $message, $code, $previous);
    }
}
