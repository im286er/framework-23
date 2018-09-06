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
use Leevel\Router\SwaggerRouter;
use Tests\TestCase;

/**
 * swagger 生成注解路由组件测试.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.04.10
 *
 * @version 1.0
 */
class SwaggerRouterTest extends TestCase
{
    public function testBaseUse()
    {
        $swaggerRouter = new SwaggerRouter($this->createMiddlewareParser(), 'queryphp.cn', 'Tests\Router\Apps');

        $scanDir = __DIR__.'/Apps/Petstore30';

        $swaggerRouter->addSwaggerScan($scanDir);
        $result = $swaggerRouter->handle();

        $data = file_get_contents($scanDir.'/router.data');

        $this->assertSame(
            $data,
            $this->varExport(
                $result
            )
        );
    }

    public function t2estBindNotFoundForParseBindBySource()
    {
        $swaggerRouter = new SwaggerRouter($this->createMiddlewareParser(), 'queryphp.cn', 'NotFound\Tests\Router\Apps');

        $scanDir = __DIR__.'/Apps/Petstore30';

        $swaggerRouter->addSwaggerScan($scanDir);
        $result = $swaggerRouter->handle();

        $data = file_get_contents($scanDir.'/router.data');

        $this->assertSame(
            $data,
            $this->varExport(
                $result
            )
        );
    }

    public function testAddSwaggerScanCheckDir()
    {
        $scanDir = __DIR__.'/Apps/PetstorenNotFound';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Swagger scandir %s is exits.', $scanDir));

        $swaggerRouter = new SwaggerRouter($this->createMiddlewareParser());
        $swaggerRouter->addSwaggerScan($scanDir);
    }

    protected function createMiddlewareParser(): MiddlewareParser
    {
        $router = $this->createMock(IRouter::class);

        $this->assertInstanceof(IRouter::class, $router);

        return new MiddlewareParser($router);
    }
}
