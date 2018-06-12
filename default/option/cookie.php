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

/*
 * cookie 默认配置文件
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2016.11.19
 * @version 1.0
 */
return [
    /*
     * ---------------------------------------------------------------
     * cookie 前缀
     * ---------------------------------------------------------------
     *
     * 设置 cookie 前缀可以用来解决冲突
     */
    'prefix' => 'q_',

    /*
     * ---------------------------------------------------------------
     * cookie 域名
     * ---------------------------------------------------------------
     *
     * Cookie 的有效域名/子域名。 设置成子域名（例如 'www.example.com'），会使 Cookie
     * 对这个子域名和它的三级域名有效（例如 w2.www.example.com）。 要让 Cookie 对整个域名
     * 有效（包括它的全部子域名），只要设置成域名就可以了（这个例子里是 'example.com'）
     * 相关技术文档：http://php.net/manual/zh/function.setcookie.php
     */
    'domain' => '',

    /*
     * ---------------------------------------------------------------
     * cookie 路径
     * ---------------------------------------------------------------
     *
     * Cookie 有效的服务器路径。 设置成 '/' 时，Cookie 对整个域名 domain 有效。 如果设置成 '/foo/'，
     * Cookie 仅仅对 domain 中 /foo/ 目录及其子目录有效（比如 /foo/bar/）。 默认值是设置 Cookie 时的当前目录
     * 相关技术文档：http://php.net/manual/zh/function.setcookie.php
     */
    'path' => '/',

    /*
     * ---------------------------------------------------------------
     * cookie 默认过期时间
     * ---------------------------------------------------------------
     *
     * Cookie 的过期时间。 这是个 Unix 时间戳，即 Unix 纪元以来（格林威治时间 1970 年 1 月 1 日 00:00:00）
     * 的秒数。 也就是说，基本可以用 time() 函数的结果加上希望过期的秒数。 或者也可以用 mktime()。 time()+60*60*24*30
     * 就是设置 Cookie 30 天后过期。 如果设置成零，或者忽略参数， Cookie 会在会话结束时过期（也就是关掉浏览器时）
     * 这里的过期时间为我们在当前时间上加上了过期的秒数量即为过期时间
     * 相关技术文档：http://php.net/manual/zh/function.setcookie.php
     */
    'expire' => 86400,

    /*
     * ---------------------------------------------------------------
     * cookie 仅 HTTP 协议访问
     * ---------------------------------------------------------------
     *
     * 设置成 TRUE，Cookie 仅可通过 HTTP 协议访问。 这意思就是 Cookie 无法通过类似 JavaScript 这样的脚本语言访问。
     * 要有效减少 XSS 攻击时的身份窃取行为，可建议用此设置（虽然不是所有浏览器都支持），不过这个说法经常有争议。 PHP 5.2.0 中添加。
     * TRUE 或 FALSE
     * 相关技术文档：http://php.net/manual/zh/function.setcookie.php
     */
    'httponly' => false,
];
