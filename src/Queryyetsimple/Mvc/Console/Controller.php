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
namespace Leevel\Mvc\Console;

use Leevel\Console\{
    Make,
    Option,
    Argument
};

/**
 * 生成控制器
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.05.03
 * @version 1.0
 */
class Controller extends Make
{

    /**
     * 命令名字
     *
     * @var string
     */
    protected $strName = 'make:controller';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $strDescription = 'Create a new controller';

    /**
     * 命令帮助
     *
     * @var string
     */
    protected $strHelp = <<<EOF
The <info>%command.name%</info> command to make controller with default_app namespace:

  <info>php %command.full_name% name</info>

You can also by using the <comment>--namespace</comment> option:

  <info>php %command.full_name% name --namespace=common</info>
EOF;

    /**
     * 响应命令
     *
     * @return void
     */
    public function handle()
    {
        // 处理命名空间路径
        $this->parseNamespace();

        // 设置模板路径
        $this->setTemplatePath(__DIR__ . '/template');

        // 保存路径
        $this->setSaveFilePath($this->getNamespacePath() . 'application/controller/' . $this->argument('name') . '.php');

        // 设置类型
        $this->setMakeType('controller');

        // 执行
        parent::handle();
    }

    /**
     * 命令参数
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                Argument::OPTIONAL,
                'This is the controller name.'
            ]
        ];
    }

    /**
     * 命令配置
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'namespace',
                null,
                option::VALUE_OPTIONAL,
                'Namespace registered to system,default namespace is these (common,home,~_~)',
                        'home'
                ]
        ];
    }
}