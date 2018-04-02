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
namespace Queryyetsimple\Mvc;

use RuntimeException;
use Queryyetsimple\View\IView as ViewIView;

/**
 * 基类控制器
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2016.11.19
 * @version 1.0
 */
abstract class controller implements IController
{

    /**
     * 视图
     *
     * @var \queryyetsimple\Mvc\IView
     */
    protected $view;

    /**
     * 构造函数
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * 设置视图
     *
     * @param \queryyetsimple\Mvc\IView $view
     * @return $this
     */
    public function setView(IView $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * 切换视图
     *
     * @param \Queryyetsimple\View\IView $theme
     * @param boolean $forever
     * @return $this
     */
    public function switchView(ViewIView $theme, bool $forever = false)
    {
        $this->checkView();
        $this->view->switchView($theme, $forever);
        return $this;
    }

    /**
     * 变量赋值
     *
     * @param mixed $name
     * @param mixed $value
     * @return $this
     */
    public function assign($name, $value = null)
    {
        $this->checkView();
        $this->view->assign($name, $value);
        return $this;
    }

    /**
     * 获取变量赋值
     *
     * @param string|null $name
     * @return mixed
     */
    public function getAssign($name = null)
    {
        $this->checkView();
        return $this->view->getAssign($name);
    }

    /**
     * 删除变量值
     *
     * @param mixed $name
     * @return $this
     */
    public function deleteAssign($name)
    {
        $this->checkView();

        call_user_func_array([
            $this->view,
            'deleteAssign'
        ], func_get_args());
        
        return $this;
    }

    /**
     * 清空变量值
     *
     * @return $this
     */
    public function clearAssign()
    {
        $this->checkView();
        $this->view->clearAssign();
        return $this;
    }

    /**
     * 加载视图文件
     *
     * @param string $file
     * @param array $vars
     * @param string $ext
     * @return string
     */
    public function display($file = null, array $vars = [], string $ext = '')
    {
        $this->checkView();
        return $this->view->display($file, $vars, $option);
    }

    /**
     * 验证 view
     *
     * @return void
     */
    protected function checkView()
    {
        if (! $this->view) {
            throw new RuntimeException('View is not set in controller');
        }
    }

    /**
     * 赋值
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->assign($key, $value);
    }

    /**
     * 获取值
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAssign($key);
    }
}