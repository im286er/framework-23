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

namespace Tests\Bootstrap\Runtime;

use Exception;
use Leevel\Bootstrap\Project as Projects;
use Leevel\Bootstrap\Runtime\Runtime;
use Leevel\Di\Container;
// use Leevel\Di\Provider;
// use Leevel\Filesystem\Fso;
use Leevel\Di\IContainer;
use Leevel\Http\IRequest;
use Leevel\Http\IResponse;
use Leevel\Log\ILog;
use Leevel\Option\Option;
use Tests\TestCase;

/**
 * runtime test.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.08.21
 *
 * @version 1.0
 */
class RuntimeTest extends TestCase
{
    public function testBaseUse()
    {
        $project = new Project($appPath = __DIR__.'/app');

        $this->assertInstanceof(IContainer::class, $project);
        $this->assertInstanceof(Container::class, $project);

        $option = new Option([
            'app' => [
                '_composer' => [
                    'i18ns' => [
                        'extend',
                    ],
                ],
            ],
            'i18n' => [
                'default' => 'en-US',
            ],
        ]);

        $project->singleton('option', function () use ($option) {
            return $option;
        });

        $log = $this->createMock(ILog::class);

        $log->method('error')->willReturn([]);
        $this->assertEquals([], $log->error('hello world', [], true));

        $project->singleton(ILog::class, function () use ($log) {
            return $log;
        });

        $runtime = new Runtime1($project);

        $e = new Exception1('hello world');

        $this->assertNull($runtime->report($e));
    }

    public function testExceptionItSelfWithReport()
    {
        $project = new Project($appPath = __DIR__.'/app');

        $runtime = new Runtime1($project);

        $e = new Exception2('hello world');

        $this->assertArrayNotHasKey('testExceptionItSelfWithReport', $_SERVER);

        $this->assertNull($runtime->report($e));

        $this->assertSame(1, $_SERVER['testExceptionItSelfWithReport']);

        unset($_SERVER['testExceptionItSelfWithReport']);
    }

    public function testRender()
    {
        $project = new Project($appPath = __DIR__.'/app');

        $request = $this->createMock(IRequest::class);

        $request->method('isCli')->willReturn(true);
        $this->assertTrue($request->isCli());

        $project->singleton('request', function () use ($request) {
            return $request;
        });

        $option = new Option([
            'app' => [
                'debug' => true,
            ],
        ]);

        $project->singleton('option', function () use ($option) {
            return $option;
        });

        $runtime = new Runtime1($project);

        $e = new Exception1('hello world');

        $this->assertInstanceof(IResponse::class, $resultResponse = $runtime->render($request, $e));

        $this->assertContains('Tests\Bootstrap\Runtime\Exception1: hello world in file', $resultResponse->getContent());
        $this->assertSame(500, $resultResponse->getStatusCode());
    }
}

class Project extends Projects
{
    protected function registerBaseProvider()
    {
    }
}

class Runtime1 extends Runtime
{
}

class Exception1 extends Exception
{
}

class Exception2 extends Exception
{
    public function report()
    {
        $_SERVER['testExceptionItSelfWithReport'] = 1;
    }
}
