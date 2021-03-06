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

namespace Leevel\Database\Console;

use Leevel\Console\Argument;
use Leevel\Console\Make;
use Leevel\Console\Option;

/**
 * 生成模型实体.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2017.05.02
 *
 * @version 1.0
 * @codeCoverageIgnore
 */
class Entity extends Make
{
    /**
     * 命令名字.
     *
     * @var string
     */
    protected $name = 'make:entity';

    /**
     * 命令描述.
     *
     * @var string
     */
    protected $description = 'Create a new entity.';

    /**
     * 命令帮助.
     *
     * @var string
     */
    protected $help = <<<'EOF'
The <info>%command.name%</info> command to make entity with project namespace:

  <info>php %command.full_name% name</info>

You can also by using the <comment>--namespace</comment> option:

  <info>php %command.full_name% name --namespace=common</info>

You can also by using the <comment>--extend</comment> option:

  <info>php %command.full_name% name action --extend=0</info>
EOF;

    /**
     * 响应命令.
     */
    public function handle()
    {
        // 处理命名空间路径
        $this->parseNamespace();

        // 设置模板路径
        $this->setTemplatePath(
            __DIR__.'/stub/'.
            ($this->option('extend') ? 'entity' : 'entity_without_extend')
        );

        // 保存路径
        $this->setSaveFilePath(
            $this->getNamespacePath().
            'Domain/Entity/'.
            ucfirst($this->argument('name')).'.php'
        );

        // 设置类型
        $this->setMakeType('entity');

        // 执行
        parent::handle();
    }

    /**
     * 命令参数.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                Argument::REQUIRED,
                'This is the entity name.',
            ],
        ];
    }

    /**
     * 命令配置.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'namespace',
                null,
                Option::VALUE_OPTIONAL,
                'Apps namespace registered to system,default namespace is these (Common,App,Admin)',
                'app',
            ],
            [
                'extend',
                null,
                Option::VALUE_OPTIONAL,
                'Entity with the code that make it extends Leevel\Database\Ddd\Entity',
                1,
            ],
        ];
    }
}
