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

namespace Tests\Log\Provider;

use Leevel\Di\Container;
use Leevel\Filesystem\Fso;
use Leevel\Log\Provider\Register;
use Leevel\Option\Option;
use Tests\TestCase;

/**
 * register test.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.08.24
 *
 * @version 1.0
 */
class RegisterTest extends TestCase
{
    public function testBaseUse()
    {
        $test = new Register($container = $this->createContainer());
        $test->register();

        $manager = $container->make('logs');

        $manager->info('foo', ['bar']);
        $filePath = __DIR__.'/cache/info/'.date('Y-m-d').'.log';
        $this->assertFileNotExists($filePath);

        $manager->flush();
        $this->assertFileExists($filePath);

        Fso::deleteDirectory(__DIR__.'/cache', true);
    }

    protected function createContainer()
    {
        $container = new Container();

        $option = new Option([
            'log' => [
                'default' => 'file',
                'level'   => [
                    'debug',
                    'info',
                    'notice',
                    'warning',
                    'error',
                    'critical',
                    'alert',
                    'emergency',
                ],
                'time_format' => '[Y-m-d H:i:s]',
                'connect'     => [
                    'file' => [
                        'driver' => 'file',
                        'name'   => 'Y-m-d',
                        'size'   => 2097152,
                        'path'   => __DIR__.'/cache',
                    ],
                ],
            ],
        ]);

        $container->singleton('option', $option);

        return $container;
    }
}