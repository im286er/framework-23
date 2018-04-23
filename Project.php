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
namespace Leevel\Bootstrap;

use Exception;
use Dotenv\Dotenv;
use RuntimeException;
use NunoMaduro\Collision\Provider as CollisionProvider;
use Leevel\{
    Psr4\Psr4,
    Di\Provider,
    Di\Container,
    Support\Facade,
    Filesystem\Fso,
    Bootstrap\Runtime\Runtime,
    Bootstrap\Console\Provider\Register as ConsoleProvider
};

/**
 * 项目管理
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.01.14
 * @version 1.0
 */
class Project extends Container implements IProject
{

    /**
     * 当前项目实例
     *
     * @var Leevel\Bootstrap\Project
     */
    protected static $project;

    /**
     * 项目配置
     *
     * @var array
     */
    protected $arrOption = [];

    /**
     * 项目框架路径
     *
     * @var string
     */
    protected $strFrameworkPath;

    /**
     * 项目基础路径
     *
     * @var string
     */
    protected $strPath;

    /**
     * 项目 APP 基本配置
     *
     * @var array
     */
    protected $arrAppOption = [];

    /**
     * 项目应用列表
     *
     * @var array
     */
    protected $apps = [];

    /**
     * 项目路由文件列表
     *
     * @var array
     */
    protected $routers = [];

    /**
     * 系统所有环境变量
     *
     * @var array
     */
    protected $envs = [];

    /**
     * 延迟载入服务提供者
     *
     * @var array
     */
    protected $arrDeferredProviders = [];

    /**
     * 服务提供者 bootstrap
     *
     * @var array
     */
    protected $arrProviderBootstrap = [];

    /**
     * 构造函数
     * 受保护的禁止外部通过 new 实例化，只能通过 singletons 生成单一实例
     *
     * @param array $arrOption
     * @return void
     */
    protected function __construct($arrOption = [])
    {
        // 项目基础配置
        $this->setOption($arrOption)->

        // 初始化项目路径
        setPath()->

        // 注册别名
        registerAlias()->

        // 载入 app 配置
        loadApp()->

        // 初始化项目
        initProject()->

        // 注册框架核心提供者
        registerMvcProvider()->

        // 注册基础提供者 register
        registerBaseProvider();
    }

    /**
     * 禁止克隆
     *
     * @return void
     */
    protected function __clone()
    {
        throw new RuntimeException('Project disallowed clone.');
    }

    /**
     * 执行项目
     *
     * @return $this
     */
    public function run()
    {
        $this->registerRuntime();

        $this->baseProviderBootstrap();

        $this->appInit();

        $this->appRouter();

        $this->appRun();

        return $this;
    }

    /**
     * 运行笑脸初始化应用
     *
     * @return void
     */
    public function appInit()
    {
        $this->make(Application::class, [
            Application::INIT_APP
        ])->bootstrap();
    }

    /**
     * 完成路由请求
     *
     * @return void
     */
    public function appRouter()
    {
        $this->router->run();
    }
    
    /**
     * 执行应用
     *
     * @param string $app
     * @return void
     */
    public function appRun($app = null)
    {
        if (! $app) {
            $app = $this->request->app();
        }

        $this->make(Application::class)->

        bootstrap($app)->

        run();
    }

    /**
     * 返回项目
     *
     * @param array $arrOption
     * @param boolean $autorun
     * @return static
     */
    public static function singletons($arrOption = [], $autorun = true)
    {
        if (static::$project !== null) {
            return static::$project;
        } else {
            static::$project = new static($arrOption);

            if ($autorun === true) {
                static::$project->run();
            }

            return static::$project;
        }
    }

    /**
     * 是否以扩展方式运行
     *
     * @return boolean
     */
    public function runWithExtension()
    {
        return extension_loaded('leevel');
    }

    /**
     * 程序版本
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function make($strFactoryName, ?array $arrArgs = null)
    {
        $strFactoryName = $this->getAlias($strFactoryName);

        if (isset($this->arrDeferredProviders[$strFactoryName])) {
            $this->registerDeferredProvider($strFactoryName);
        }

        return parent::make($strFactoryName, $arrArgs);
    }

    /**
     * 系统所有应用
     *
     * @return array
     */
    public function apps()
    {
        return $this->apps;
    }

    /**
     * 系统所有路由文件列表
     *
     * @return array
     */
    public function routers()
    {
        return $this->routers;
    }

    /**
     * 系统所有环境变量
     *
     * @return array
     */
    public function envs()
    {
        return $this->envs;
    }

    /**
     * 基础路径
     *
     * @return string
     */
    public function path()
    {
        return $this->strPath;
    }

    /**
     * 框架路径
     *
     * @return string
     */
    public function pathFramework()
    {
        return $this->strFrameworkPath;
    }

    /**
     * 应用路径
     *
     * @return string
     */
    public function pathApplication()
    {
        return $this->arrOption['path_application'] ?? $this->strPath . DIRECTORY_SEPARATOR . 'application';
    }

    /**
     * 系统错误、异常、调试和跳转模板路径
     *
     * @param string $type
     * @return string
     */
    public function pathSystem($type)
    {
        $types = [
            'error',
            'exception',
            'trace'
        ];

        if (! in_array($type, $types)) {
            throw new Exception(sprintf('System type %s not support', $type));
        }

        $path = $this->arrAppOption['system_path'] ?? '';
        $file = $this->arrAppOption['system_template'][$type] ?? $type . '.php';

        if ( !is_dir($path)) {
            $path = $this->path() . '/' . $path;
        }

        return $path . '/' . $file;
    }

    /**
     * 公共路径
     *
     * @return string
     */
    public function pathCommon()
    {
        return $this->arrOption['path_common'] ?? $this->strPath . DIRECTORY_SEPARATOR . 'common';
    }

    /**
     * 运行路径
     *
     * @return string
     */
    public function pathRuntime()
    {
        return $this->arrOption['path_runtime'] ?? $this->strPath . DIRECTORY_SEPARATOR . 'runtime';
    }

    /**
     * 资源路径
     *
     * @return string
     */
    public function pathPublic()
    {
        return $this->arrOption['path_public'] ?? $this->strPath . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * 附件路径
     *
     * @return string
     */
    public function pathStorage()
    {
        return $this->arrOption['path_storage'] ?? $this->strPath . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * 应用路径
     *
     * @return string
     */
    public function pathApplicationCurrent()
    {
        return $this->pathApplication() . '/' . strtolower($this->request->app());
    }

    /**
     * 取得应用缓存目录
     *
     * @param string $strType
     * @return string
     */
    public function pathApplicationCache($strType)
    {
        $arrType = [
            'file',
            'log',
            'table',
            'theme',
            'option',
            'i18n',
            'router',
            'console',
            'swoole'
        ];

        if (! in_array($strType, $arrType)) {
            throw new Exception(sprintf('Application cache type %s not support', $strType));
        }

        return $this->pathRuntime() . '/' . $strType;
    }

    /**
     * 取得应用目录
     *
     * @param string $strType
     * @return string
     */
    public function pathApplicationDir($strType)
    {
        $arrType = [
            'option',
            'theme',
            'i18n'
        ];

        if (! in_array($strType, $arrType)) {
            throw new Exception(sprintf('Application dir type %s not support', $strType));
        }

        return $this->pathApplicationCurrent() . '/ui/' . $strType;
    }

    /**
     * 取得 composer
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public function composer() {
        return require $this->strPath . '/vendor/autoload.php';
    }

    /**
     * 获取命名空间路径
     *
     * @param string $namespaces
     * @return string|null
     */
    public function getPathByNamespace($namespaces)
    {
        $namespaces = explode('\\', $namespaces);

        $prefix = $this->composer()->getPrefixesPsr4();
        if (! isset($prefix[$namespaces[0] . '\\'])) {
            return null;
        }

        $namespaces[0] = $prefix[$namespaces[0] . '\\'][0];
        return implode('/', $namespaces);
    }

    /**
     * 是否开启 debug
     *
     * @return boolean
     */
    public function debug()
    {
        return $this->arrAppOption['debug'] ?? false;
    }

    /**
     * 是否为开发环境
     *
     * @return string
     */
    public function development()
    {
        return $this->arrAppOption['environment'] == 'development';
    }

    /**
     * 运行环境
     *
     * @return boolean
     */
    public function environment()
    {
        return $this->arrAppOption['environment'];
    }

    /**
     * 是否为 API
     *
     * @return boolean
     */
    public function api()
    {
        return $this->arrAppOption['default_response'] == 'api';
    }

    /**
     * 是否为 Console
     *
     * @return boolean
     */
    public function console()
    {
        return env('app_name') == 'frameworkconsole';
    }

    /**
     * 返回应用配置
     *
     * @return array
     */
    public function appOption() {
        return $this->arrAppOption;
    }

    /**
     * 创建服务提供者
     *
     * @param string $strProvider
     * @return \leevel\Di\Provider
     */
    public function makeProvider($strProvider)
    {
        return new $strProvider($this);
    }

    /**
     * 执行 bootstrap
     *
     * @param \leevel\Di\Provider $objProvider
     * @return void
     */
    public function callProviderBootstrap(Provider $objProvider)
    {
        if (! method_exists($objProvider, 'bootstrap')) {
            return;
        }

        $this->call([
            $objProvider,
            'bootstrap'
        ]);
    }

    /**
     * 设置项目基础配置
     *
     * @param array $arrOption
     * @return $this
     */
    protected function setOption($arrOption)
    {
        $this->arrOption = $arrOption;

        return $this;
    }

    /**
     * 载入 APP 配置
     *
     * @return $this
     */
    protected function loadApp()
    {
        $strCache = $this->appOptionCachePath();

        if (is_file($strCache) && $this->checkEnv($strCache)) {
            Fso::deleteDirectory(dirname($strCache), true);
        }

        $this->loadAppOption($strCache);

        return $this;
    }

    /**
     * 初始化处理
     *
     * @return $this
     */
    protected function initProject()
    {
        if ($this->development()) {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ERROR | E_PARSE | E_STRICT);
        }

        ini_set('default_charset', 'utf8');

        Facade::setContainer($this);

        // 载入 project 引导文件
        if (is_file(($strBootstrap = $this->pathCommon() . '/bootstrap.php'))) {
            require_once $strBootstrap;
        }

        return $this;
    }

    /**
     * 框架基础提供者 register
     *
     * @return $this
     */
    protected function registerBaseProvider()
    {
        $booCache = false;

        $strCachePath = $this->defferProviderCachePath();
        if (! $this->development() && is_file($strCachePath)) {
            list($this->arrDeferredProviders, $arrDeferredAlias) = include $strCachePath;
            $booCache = true;
        } else {
            $arrDeferredAlias = [];
        }

        foreach ($this->arrAppOption['provider'] as $strProvider) {
            if ($booCache === true && isset($arrDeferredAlias[$strProvider])) {
                $this->alias($arrDeferredAlias[$strProvider]);
                continue;
            }

            if (! class_exists($strProvider)) {
                continue;
            }

            if ($strProvider::isDeferred()) {
                $arrProviders = $strProvider::providers();
                foreach ($arrProviders as $mixKey => $mixAlias) {
                    if (is_int($mixKey)) {
                        $mixKey = $mixAlias;
                    }
                    $this->arrDeferredProviders[$mixKey] = $strProvider;
                }
                $this->alias($arrProviders);
                $arrDeferredAlias[$strProvider] = $arrProviders;
                continue;
            }

            $objProvider = $this->makeProvider($strProvider);
            $objProvider->register();

            if (method_exists($objProvider, 'bootstrap')) {
                $this->arrProviderBootstrap[] = $objProvider;
            }
        }

        $cacheDir = dirname($strCachePath);
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        if ($this->development() || ! is_file($strCachePath)) {
            file_put_contents($strCachePath, '<?' . 'php /* ' . date('Y-m-d H:i:s') . ' */ ?' . '>' . 
                PHP_EOL . '<?' . 'php return ' . var_export([
                $this->arrDeferredProviders,
                $arrDeferredAlias
            ], true) . '; ?' . '>');

            chmod($strCachePath, 0777);
        }

        return $this;
    }

    /**
     * 执行框架基础提供者 bootstrap
     *
     * @return $this
     */
    protected function baseProviderBootstrap()
    {
        foreach ($this->arrProviderBootstrap as $obj) {
            $this->callProviderBootstrap($obj);
        }

        return $this;
    }

    /**
     * 框架 MVC 基础提供者
     *
     * @return $this
     */
    protected function registerMvcProvider()
    {
        // 注册本身
        $this->instance('project', $this);

        // 注册 Application
        $this->singleton(Application::class, function ($container, $sApp) {
            return new Application($container, $sApp);
        });

        // 注册 console
        if ($this->console()) {
            $this->makeProvider(ConsoleProvider::class)->register();
        }

        return $this;
    }

    /**
     * 注册别名
     *
     * @return void
     */
    protected function registerAlias()
    {
        $this->alias([
            'project' => [
                'Leevel\Bootstrap\Project',
                'Leevel\Di\IContainer',
                'Leevel\Bootstrap\IProject',
                'app'
            ]
        ]);

        return $this;
    }

    /**
     * QueryPHP 系统错误处理
     *
     * @return void
     */
    protected function registerRuntime()
    {
        if (PHP_SAPI == 'cli') {
            (new CollisionProvider)->register();
            return;
        }

        Runtime::container($this);

        register_shutdown_function([
            'Leevel\Bootstrap\Runtime\Runtime', 
            'shutdownHandle'
        ]);
        
        set_error_handler([
            'Leevel\Bootstrap\Runtime\Runtime', 
            'errorHandle'
        ]);
        
        set_exception_handler([
            'Leevel\Bootstrap\Runtime\Runtime', 
            'exceptionHandle'
        ]);
    }

    /**
     * 初始化项目路径
     *
     * @param string $strPath
     * @return $this
     */
    protected function setPath()
    {
        // 框架路径
        $this->strFrameworkPath = dirname(__DIR__);

        // 基础路径
        $this->strPath = dirname(__DIR__, 6);

        // 验证缓存路径
        if (! is_writeable($this->pathRuntime())) {
            throw new RuntimeException(sprintf('Runtime path %s is not writeable.', $this->pathRuntime()));
        }

        return $this;
    }

    /**
     * 注册延迟载入服务提供者
     *
     * @param string $strProvider
     * @return void
     */
    protected function registerDeferredProvider($strProvider)
    {
        if (! isset($this->arrDeferredProviders[$strProvider])) {
            return;
        }

        $objProvider = $this->makeProvider($this->arrDeferredProviders[$strProvider]);
        $objProvider->register();

        if (method_exists($objProvider, 'bootstrap')) {
            $this->callProviderBootstrap($objProvider);
        }

        unset($this->arrDeferredProviders[$strProvider]);
    }

    /**
     * 返回延迟服务提供者缓存路径
     *
     * @return string
     */
    protected function defferProviderCachePath()
    {
        return $this->pathRuntime() . '/provider/deffer.php';
    }

    /**
     * 载入 APP 配置
     *
     * @param string $strCache
     * @return void
     */
    protected function loadAppOption($strCache)
    {
        if (is_file($strCache) && is_array($arrOption = include $strCache)) {
            $this->arrAppOption = $arrOption['app'];
            $this->envs = $this->environmentVariables($this->arrAppOption['~envs~']);
            $this->apps = $this->arrAppOption['~apps~'];
            $this->routers = $this->arrAppOption['~routers~'];
        } else {
            $this->arrAppOption = ( array ) include $this->pathCommon() . '/ui/option/app.php';
            $this->envs = $this->environmentVariables();
            $this->apps = Fso::lists($this->pathApplication());
            $this->routers = Fso::lists($this->pathCommon() . '/ui/router', 'file', true, [], [
                'php'
            ]);
        }
    }

    /**
     * 系统缓存路径
     *
     * @return string
     */
    protected function appOptionCachePath()
    {
        return $this->pathApplicationCache('option') . '/' . Application::INIT_APP . '.php';
    }

    /**
     * 验证环境变量是否变动
     *
     * @param string $strCache
     * @return void
     */
    protected function checkEnv($strCache)
    {
        return filemtime($this->path() . '/.env') > filemtime($strCache);
    }

    /**
     * 设置环境变量
     *
     * @param array $arrEnv
     * @return array
     */
    protected function environmentVariables($arrEnv = [])
    {
        if ($arrEnv) {
            foreach ($arrEnv as $strName => $strValue) {
                $this->setEnvironmentVariable($strName, $strValue);
            }

            return $arrEnv;
        } else {
            $objDotenv = new Dotenv($this->path());
            $objDotenv->load();
            
            return $_ENV;
        }
    }

    /**
     * 设置单个环境变量
     *
     * @param string $strName
     * @param string|null $mixValue
     * @return void
     */
    protected function setEnvironmentVariable($strName, $mixValue = null)
    {
        if (is_bool($mixValue)) {
            putenv($strName . '=' . ($mixValue ? '(true)' : '(false)'));
        } elseif (is_null($mixValue)) {
            putenv($strName . '(null)');
        } else {
            putenv($strName . '=' . $mixValue);
        }

        $_ENV[$strName] = $mixValue;
        $_SERVER[$strName] = $mixValue;
    }
}
