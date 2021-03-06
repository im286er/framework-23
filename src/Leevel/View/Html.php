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

namespace Leevel\View;

use RuntimeException;

/**
 * html 模板处理类.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2016.11.18
 *
 * @version 1.0
 */
class Html extends Connect implements IConnect
{
    /**
     * 视图分析器.
     *
     * @var \Leevel\View\iparser
     */
    protected $parser;

    /**
     * 解析 parse.
     *
     * @var callable
     */
    protected $parseResolver;

    /**
     * 配置.
     *
     * @var array
     */
    protected $option = [
        'theme_path'            => '',
        'suffix'                => '.html',
        'cache_path'            => '',
    ];

    /**
     * 加载视图文件.
     *
     * @param string $file    视图文件地址
     * @param array  $vars
     * @param string $ext     后缀
     * @param bool   $display 是否显示
     *
     * @return string|void
     */
    public function display(string $file, array $vars = [], ?string $ext = null, bool $display = true)
    {
        // 加载视图文件
        $file = $this->parseDisplayFile($file, $ext);

        // 变量赋值
        if ($vars) {
            $this->setVar($vars);
        }

        if (is_array($this->vars) && !empty($this->vars)) {
            extract($this->vars, EXTR_PREFIX_SAME, 'q_');
        }

        $cachepath = $this->getCachePath($file); // 编译文件路径

        if ($this->isCacheExpired($file, $cachepath)) { // 重新编译
            $this->parser()->doCompile($file, $cachepath);
        }

        // 返回类型
        if (false === $display) {
            ob_start();
            include $cachepath;
            $result = ob_get_contents();
            ob_end_clean();

            return $result;
        }

        include $cachepath;
    }

    /**
     * 设置 parse 解析回调.
     *
     * @param callable $parseResolver
     */
    public function setParseResolver(callable $parseResolver)
    {
        $this->parseResolver = $parseResolver;
    }

    /**
     * 获取编译路径.
     *
     * @param string $file
     *
     * @return string
     */
    public function getCachePath(string $file)
    {
        if (!$this->option['cache_path']) {
            throw new RuntimeException('Theme cache path must be set.');
        }

        $file = str_replace('//', '/', str_replace('\\', '/', $file));

        $file = basename($file, '.'.pathinfo($file, PATHINFO_EXTENSION)).'.'.md5($file).'.php';

        return $this->option['cache_path'].'/'.$file;
    }

    /**
     * 解析 parse.
     *
     * @return \Leevel\View\IParser
     */
    protected function resolverParser()
    {
        if (!$this->parseResolver) {
            throw new RuntimeException('Html theme not set parse resolver.');
        }

        return call_user_func($this->parseResolver);
    }

    /**
     * 获取分析器.
     *
     * @return \Leevel\View\IParser
     */
    protected function parser()
    {
        if (null !== $this->parser) {
            return $this->parser;
        }

        return $this->parser = $this->resolverParser();
    }

    /**
     * 判断缓存是否过期
     *
     * @param string $file
     * @param string $cachepath
     *
     * @return bool
     */
    protected function isCacheExpired(string $file, string $cachepath)
    {
        if (!is_file($cachepath)) {
            return true;
        }

        if (filemtime($file) >= filemtime($cachepath)) {
            return true;
        }

        return false;
    }
}
