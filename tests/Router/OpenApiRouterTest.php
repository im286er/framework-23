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

namespace Tests\Router;

use Leevel\Router\IRouter;
use Leevel\Router\MiddlewareParser;
use Leevel\Router\OpenApiRouter;
use Tests\TestCase;

/**
 * openApi 生成注解路由组件测试.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.04.10
 *
 * @version 1.0
 */
class OpenApiRouterTest extends TestCase
{
    public function testBaseUse()
    {
        $openApiRouter = new OpenApiRouter($this->createMiddlewareParser(), 'queryphp.cn', 'Tests\Router\Apps');

        $scandir = __DIR__.'/Apps/Petstore30';

        $openApiRouter->addScandir($scandir);
        $result = $openApiRouter->handle();

        $data = file_get_contents($scandir.'/router.data');

        $this->assertSame(
            $data,
            $this->varExport(
                $result
            )
        );
    }

    public function t2estBindNotFoundForParseBindBySource()
    {
        $openApiRouter = new OpenApiRouter($this->createMiddlewareParser(), 'queryphp.cn', 'NotFound\Tests\Router\Apps');

        $scandir = __DIR__.'/Apps/Petstore30';

        $openApiRouter->addScandir($scandir);
        $result = $openApiRouter->handle();

        $data = file_get_contents($scandir.'/router.data');

        $this->assertSame(
            $data,
            $this->varExport(
                $result
            )
        );
    }

    public function testAddScandirButNotFound()
    {
        $scandir = __DIR__.'/Apps/PetstorenNotFound';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('OpenApi scandir %s is exits.', $scandir));

        $openApiRouter = new OpenApiRouter($this->createMiddlewareParser());
        $openApiRouter->addScandir($scandir);
    }

    protected function createMiddlewareParser(): MiddlewareParser
    {
        $router = $this->createMock(IRouter::class);

        $this->assertInstanceof(IRouter::class, $router);

        return new MiddlewareParser($router);
    }
}