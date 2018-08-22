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

namespace Tests\Bootstrap;

use Leevel as Leevels;
use Leevel\Bootstrap\Project as Projects;
use Leevel\Di\Container;
use Leevel\Di\IContainer;
use Leevel\Kernel\IProject;
use Leevel\Log\ILog;
use Tests\TestCase;

/**
 * function test.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.08.22
 *
 * @version 1.0
 */
class FunctionTest extends TestCase
{
    public function testBaseUse()
    {
        $project = Leevel::project();

        $this->assertInstanceof(IContainer::class, $project);
        $this->assertInstanceof(Container::class, $project);
        $this->assertInstanceof(Projects::class, $project);

        // 等效
        $this->assertInstanceof(Projects::class, $project->make('project'));
        $this->assertSame('fooNotFound', $project->make('fooNotFound'));
        $this->assertInstanceof(Projects::class, Leevel::project('project'));
        $this->assertSame('fooNotFound', Leevel::project('fooNotFound'));
        $this->assertInstanceof(Projects::class, Leevel::app('project'));
        $this->assertSame('fooNotFound', Leevel::app('fooNotFound'));
    }

    public function testDd()
    {
        ob_start();
        Leevel::dd('hello_world', true);
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertContains('hello_world', $result);
        $this->assertContains('string', $result);
    }

    public function testCallStaticException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Method notFoundCallback is not exits.'
        );

        Leevel::notFoundCallback();
    }

    /**
     * @dataProvider envProvider
     *
     * @param string $name
     * @param mixed  $value
     * @param mixed  $envValue
     */
    public function testEnv(string $name, $value, $envValue)
    {
        $name = 'test_env_'.$name;

        putenv($name.'='.$value);

        $this->assertSame($envValue, Leevel::env($name));

        unset($_ENV[$name]);
    }

    public function envProvider()
    {
        return [
            ['bar', 'true', true],
            ['bar', '(true)', true],
            ['bar', 'false', false],
            ['bar', '(false)', false],
            ['bar', 'empty', ''],
            ['bar', '(empty)', ''],
            ['bar', 'null', null],
            ['bar', '(null)', null],
            ['bar', '"hello"', 'hello'],
            ['bar', "'hello'", "'hello'"],
            ['bar', true, '1'],
            ['bar', false, ''],
            ['bar', 1, '1'],
            ['bar', '', ''],
        ];
    }

    public function testEnvWithValue()
    {
        $this->assertNull(Leevel::env('testNotFound'));
        $this->assertSame('foo', Leevel::env('testNotFound', 'foo'));
        $this->assertSame('e10adc3949ba59abbe56e057f20f883e', Leevel::env('foo', function () {
            return md5('123456');
        }));
        $this->assertSame('__fooBar', Leevel::env('foo', '__fooBar'));
    }

    public function testLog()
    {
        $log = $this->createMock(ILog::class);

        $log->method('write')->willReturn(['foo', '[]']);
        $this->assertSame(['foo', '[]'], $log->write(ILog::INFO, 'foo', []));

        $log->method('log')->willReturn(['bar', '[]']);
        $this->assertSame(['bar', '[]'], $log->log('bar', []));

        $project = new Project3();

        $project->singleton('log', function () use ($log) {
            return $log;
        });

        Leevel2::setProject($project);

        $this->assertInstanceof(ILog::class, Leevel2::log(null));
        $this->assertSame(['bar', '[]'], Leevel2::log('bar', [], ILog::INFO));
        $this->assertSame(['foo', '[]'], Leevel2::log('foo', [], ILog::INFO, true));
    }
}

class Project2 extends Projects
{
    protected function registerBaseProvider()
    {
    }
}

class Project3 extends Projects
{
    protected function registerBaseProvider()
    {
    }
}

class Leevel extends Leevels
{
    protected static function singletons(): IContainer
    {
        return new Project2($appPath = __DIR__.'/app');
    }
}

class Leevel2 extends Leevels
{
    protected static $project;

    public static function setProject(IProject $project)
    {
        self::$project = $project;
    }

    protected static function singletons(): IContainer
    {
        return self::$project;
    }
}
