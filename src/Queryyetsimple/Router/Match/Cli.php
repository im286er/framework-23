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
namespace Queryyetsimple\Router\Match;

use Queryyetsimple\Http\Request;
use Queryyetsimple\Router\Router;
use Queryyetsimple\Console\Cli as ConsoleCli;
 
/**
 * 路由命令行匹配
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2018.02.15
 * @version 1.0
 */
class Cli
{

    /**
     * 匹配路径
     *
     * @param \Queryyetsimple\Router\Router $route
     * @param \Queryyetsimple\Http\Request $request
     * @return array
     */
    public function matche(Router $router, Request $request)
    { 
        list($node, $querys, $options) = (new ConsoleCli)->parse();

        $result = [];

        if ($node) {
            $result = $router->parseNodeUrl($node);
        }

        if ($querys) {
            $result = array_merge($result, $querys);
        }

        $result[Router::PARAMS] = $options;

        return $result;
    }
}