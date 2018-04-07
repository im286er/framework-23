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
namespace Leevel\Event;

/**
 * IDispatch 接口
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.07.12
 * @version 1.0
 */
interface IDispatch
{

    /**
     * 执行一个事件
     *
     * @param string|object $event
     * @param array $params
     * @return void
     */
    public function run($event, ...$params);

    /**
     * 注册监听器
     *
     * @param string|array $event
     * @param mixed $listener
     * @param int $priority
     * @return void
     */
    public function listeners($event, $listener, int $priority = 500);

    /**
     * 获取一个事件监听器
     *
     * @param string $event
     * @return array
     */
    public function getListeners($event);

    /**
     * 判断事件监听器是否存在
     *
     * @param string $event
     * @return bool
     */
    public function hasListeners($event);

    /**
     * 删除一个事件所有监听器
     *
     * @param string $event
     * @return void
     */
    public function deleteListeners($event);

    /**
     * 设置是否严格匹配事件
     *
     * @param bool $event
     * @return $this
     */
    public function strict(bool $strict);
}