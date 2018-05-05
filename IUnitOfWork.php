<?php declare(strict_types=1);
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
namespace Leevel\Mvc;

/**
 * 工作单元接口
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.10.14
 * @version 1.0
 */
interface IUnitOfWork
{

    /**
     * 启动事物
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * 事务回滚
     *
     * @return void
     */
    public function rollback();

    /**
     * 事务自动提交
     *
     * @return void
     */
    public function commit();

    /**
     * 事务回滚
     *
     * @param callable $calAction
     * @return mixed
     */
    public function transaction($calAction);

    /**
     * 是否已经提交事务
     *
     * @return boolean
     */
    public function committed();

    /**
     * 注册事务提交
     *
     * @return void
     */
    public function registerCommit();

    /**
     * 注册新建
     *
     * @param \Leevel\Mvc\IAggregateRoot $objEntity
     * @param \Leevel\Mvc\IRepository $objRepository
     * @return $this
     */
    public function registerCreate(IAggregateRoot $objEntity, IRepository $objRepository);

    /**
     * 注册更新
     *
     * @param \Leevel\Mvc\IAggregateRoot $objEntity
     * @param \Leevel\Mvc\IRepository $objRepository
     * @return $this
     */
    public function registerUpdate(IAggregateRoot $objEntity, IRepository $objRepository);

    /**
     * 注册删除
     *
     * @param \Leevel\Mvc\IAggregateRoot $objEntity
     * @param \Leevel\Mvc\IRepository $objRepository
     * @return $this
     */
    public function registerDelete(IAggregateRoot $objEntity, IRepository $objRepository);
}
