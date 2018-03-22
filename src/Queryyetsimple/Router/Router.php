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
namespace Queryyetsimple\Router;

use Closure;
use Exception;
use RuntimeException;
use ReflectionMethod;
use ReflectionException;
use InvalidArgumentException;
use Queryyetsimple\{
    Http\Request,
    Di\IContainer,
    Http\Response,
    Option\TClass,
    Support\TMacro,
    Mvc\IController,
    Pipeline\Pipeline
};

/**
 * 路由解析
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.01.10
 * @version 1.0
 */
class Router implements IRouter
{
    use TMacro;

    use TClass;

    /**
     * IOC Container
     *
     * @var \Queryyetsimple\Di\IContainer
     */
    protected $objContainer;

    /**
     * http 请求
     *
     * @var \Queryyetsimple\Http\Request
     */
    protected $objRequest;

    /**
     * 注册域名
     *
     * @var array
     */
    protected $arrDomains = [];

    /**
     * 注册路由
     *
     * @var array
     */
    protected $arrRouters = [];

    /**
     * 参数正则
     *
     * @var array
     */
    protected $arrWheres = [];

    /**
     * 域名正则
     *
     * @var array
     */
    protected $arrDomainWheres = [];

    /**
     * 分组传递参数
     *
     * @var array
     */
    protected $arrGroupArgs = [];

    /**
     * 路由绑定资源
     *
     * @var string
     */
    protected $arrBinds = [];

    /**
     * 域名匹配数据
     *
     * @var array
     */
    protected $arrDomainData = [];

    /**
     * 路由缓存路径
     *
     * @var string
     */
    protected $strCachePath;

    /**
     * 路由 development
     *
     * @var boolean
     */
    protected $booDevelopment = false;

    /**
     * 应用名字
     *
     * @var string
     */
    protected $strApp;

    /**
     * 控制器名字
     *
     * @var string
     */
    protected $strController;

    /**
     * 方法名字
     *
     * @var string
     */
    protected $strAction;

    /**
     * 路由绑定中间件
     *
     * @var array
     */
    protected $arrMiddlewares = [];

    /**
     * 当前的中间件
     *
     * @var array
     */
    protected $arrCurrentMiddleware;

    /**
     * HTTP 方法
     *
     * @var array
     */
    protected $arrMethods = [];

    /**
     * 路由匹配变量
     *
     * @var array
     */
    protected $arrVariable = [];

    /**
     * 匹配域名成功后的路由
     *
     * @var array
     */
    protected $arrDomainRouters = [];

    /**
     * 路由匹配数据
     * 
     * @var array
     */
    protected $matcheData = [];

    /**
     * 配置
     *
     * @var array
     */
    protected $arrOption = [
        'apps' => [
            '~_~',
            'home'
        ],
        'default_app' => 'home',
        'default_controller' => 'index',
        'default_action' => 'index',
        'router_cache' => true,
        'model' => 'pathinfo',
        'router_domain_on' => true,
        'router_domain_top' => '',
        'pathinfo_restful' => true,
        'args_protected' => [],
        'args_regex' => [],
        'args_strict' => false,
        'middleware_strict' => false,
        'method_strict' => false,
        'controller_dir' => 'app/controller',

        // 路由分组
        'middleware_group' => [],

        // 路由别名
        'middleware_alias' => []
    ];

    /**
     * 默认替换参数[字符串]
     *
     * @var string
     */
    const DEFAULT_REGEX = '\S+';

    /**
     * 构造函数
     *
     * @param \Queryyetsimple\Di\IContainer $objContainer
     * @param \Queryyetsimple\Http\Request $objRequest
     * @param array $arrOption
     * @return void
     */
    public function __construct(IContainer $objContainer, request $objRequest, array $arrOption = [])
    {
        $this->objContainer = $objContainer;
        $this->objRequest = $objRequest;
        $this->options($arrOption);
    }

    /**
     * 执行请求
     *
     * @return $this
     */
    public function run()
    {
        // 初始化
        $this->initRequest();

        // 匹配路由
        $this->matchRouter();

        // 验证 HTTP 方法
        $this->validateMethod();

        // 穿越中间件
        $this->throughMiddleware($this->objRequest);
    }

    /**
     * 取回应用名
     *
     * @return string
     */
    public function app()
    {
        if ($this->strApp) {
            return $this->strApp;
        } else {
            if (($this->strApp = env('app_name'))) {
                return $this->strApp;
            }

            return $this->strApp = $this->matcheData[static::APP] ?? $this->getOption('default_app');
        }
    }

    /**
     * 取回控制器名
     *
     * @return string
     */
    public function controller()
    {
        if ($this->strController) {
            return $this->strController;
        } else {
            if (($this->strController = env('controller_name'))) {
                return $this->strController;
            }

            return $this->matcheData[static::CONTROLLER] ?? $this->getOption('default_controller');
        }
    }

    /**
     * 取回方法名
     *
     * @return string
     */
    public function action()
    {
        if ($this->strAction) {
            return $this->strAction;
        } else {
            if (($this->strAction = env('action_name'))) {
                return $this->strAction;
            }

            return $this->matcheData[static::ACTION] ?? $this->getOption('default_action');
        }
    }

    /**
     * 取回控制器前缀
     *
     * @return string
     */
    public function prefix()
    {
        return $this->matcheData[static::PREFIX] ?? '';
    }

    /**
     * 取回匹配参数
     *
     * @return string
     */
    public function params()
    {
        return $this->matcheData[static::PARAMS] ?? [];
    }

    /**
     * 穿越中间件
     *
     * @param \Queryyetsimple\Http\Request $objPassed
     * @param array $arrPassedExtend
     * @return void
     */
    public function throughMiddleware(request $objPassed, array $arrPassedExtend = [])
    {
        if (is_null($this->arrCurrentMiddleware)) {
            $this->arrCurrentMiddleware = $this->getMiddleware($this->packageNode());
        }

        if (! $this->arrCurrentMiddleware) {
            return;
        }

        $arrCurrentMiddleware = $this->arrCurrentMiddleware;
        $strMethod = ! $arrPassedExtend ? 'handle' : 'terminate';
        $arrCurrentMiddleware = array_map(function ($strItem) use ($strMethod) {
            if (! method_exists($strItem, $strMethod)) {
                return '';
            }

            if (strpos($strItem, ':') === false) {
                return $strItem . '@' . $strMethod;
            } else {
                return str_replace(':', '@' . $strMethod . ':', $strItem);
            }
        }, $arrCurrentMiddleware);
        $arrCurrentMiddleware = array_filter($arrCurrentMiddleware);

        if ($arrCurrentMiddleware) {
            (new Pipeline($this->objContainer))->

            send($objPassed)->

            send($arrPassedExtend)->

            through($arrCurrentMiddleware)->

            then();
        }
    }

    /**
     * 导入路由规则
     *
     * @param mixed $mixRouter
     * @param string $strUrl
     * @param arra $arrOption
     * @sub string domain 域名
     * @sub array params 参数
     * @sub array where 参数正则
     * @sub boolean prepend 插入顺序
     * @sub boolean strict 严格模式，启用将在匹配正则 $
     * @sub string prefix 前缀
     * @return void
     */
    public function import($mixRouter, $strUrl = '', $arrOption = [])
    {
        if (! $this->checkExpired()) {
            return;
        }

        $arrOption = $this->mergeOption([
            'prepend' => false,
            'where' => [],
            'params' => [],
            'domain' => '',
            'prefix' => ''
        ], $this->mergeOption($this->arrGroupArgs, $arrOption));

        // 支持数组传入
        if (! is_array($mixRouter) || count($mixRouter) == count($mixRouter, 1)) {
            $strTemp = $mixRouter;
            $mixRouter = [];
            if (is_string($strTemp)) {
                $mixRouter[] = [
                    $strTemp,
                    $strUrl,
                    $arrOption
                ];
            } else {
                if ($strUrl || ! empty($strTemp[1])) {
                    $mixRouter[] = [
                        $strTemp[0],
                        (! empty($strTemp[1]) ? $strTemp[1] : $strUrl),
                        $arrOption
                    ];
                }
            }
        } else {
            foreach ($mixRouter as $intKey => $arrRouter) {
                if (! is_array($arrRouter) || count($arrRouter) < 2) {
                    continue;
                }
                if (! isset($arrRouter[2])) {
                    $arrRouter[2] = [];
                }
                if (! $arrRouter[1]) {
                    $arrRouter[1] = $strUrl;
                }
                $arrRouter[2] = $this->mergeOption($arrOption, $arrRouter[2]);
                $mixRouter[$intKey] = $arrRouter;
            }
        }

        foreach ($mixRouter as $arrArgs) {
            $strPrefix = ! empty($arrArgs[2]['prefix']) ? $arrArgs[2]['prefix'] : '';
            $arrArgs[0] = $strPrefix . $arrArgs[0];

            $arrRouter = [
                'url' => $arrArgs[1],
                'regex' => $arrArgs[0],
                'params' => $arrArgs[2]['params'],
                'where' => $this->arrWheres,
                'domain' => $arrArgs[2]['domain']
            ];

            if (isset($arrArgs[2]['strict'])) {
                $arrRouter['strict'] = $arrArgs[2]['strict'];
            }

            // 合并参数正则
            if (! empty($arrArgs[2]['where']) && is_array($arrArgs[2]['where'])) {
                $arrRouter['where'] = $this->mergeWhere($arrRouter['where'], $arrArgs[2]['where']);
            }

            if (! isset($this->arrRouters[$arrArgs[0]])) {
                $this->arrRouters[$arrArgs[0]] = [];
            }

            // 优先插入
            if ($arrArgs[2]['prepend'] === true) {
                array_unshift($this->arrRouters[$arrArgs[0]], $arrRouter);
            } else {
                array_push($this->arrRouters[$arrArgs[0]], $arrRouter);
            }

            // 域名支持
            if (! empty($arrRouter['domain'])) {
                $arrOption['router'] = true;
                $this->domain($arrRouter['domain'], $arrArgs[0], $arrOption);
            }
        }
    }

    /**
     * 注册全局参数正则
     *
     * @param mixed $mixRegex
     * @param string $strValue
     * @return void
     */
    public function regex($mixRegex, $strValue = '')
    {
        if (! $this->checkExpired()) {
            return;
        }

        if (is_string($mixRegex)) {
            $this->arrWheres[$mixRegex] = $strValue;
        } else {
            $this->arrWheres = $this->mergeWhere($this->arrWheres, $mixRegex);
        }
    }

    /**
     * 注册全局域名参数正则
     *
     * @param mixed $mixRegex
     * @param string $strValue
     * @return void
     */
    public function regexDomain($mixRegex, $strValue = '')
    {
        if (! $this->checkExpired()) {
            return;
        }

        if (is_string($mixRegex)) {
            $this->arrDomainWheres[$mixRegex] = $strValue;
        } else {
            $this->arrDomainWheres = $this->mergeWhere($this->arrDomainWheres, $mixRegex);
        }
    }

    /**
     * 注册域名
     *
     * @param string $strDomain
     * @param mixed $mixUrl
     * @param array $arrOption
     * @sub array params 扩展参数
     * @sub array domain_where 域名参数
     * @sub boolean prepend 插入顺序
     * @sub string router 对应路由规则
     * @return void
     */
    public function domain($strDomain, $mixUrl, $arrOption = [])
    {
        if (! $this->checkExpired()) {
            return;
        }

        $arrOption = $this->mergeOption([
            'prepend' => false,
            'params' => [],
            'domain_where' => [],
            'router' => false
        ], $arrOption);

        // 闭包直接转接到分组
        if ($mixUrl instanceof Closure) {
            $arrOption['domain'] = $strDomain;
            $this->group($arrOption, $mixUrl);
        }

        // 注册域名
        else {
            $arrDomain = [
                'url' => $mixUrl,
                'params' => $arrOption['params'],
                'router' => $arrOption['router']
            ];

            // 合并参数正则
            $arrDomainWheres = $this->arrDomainWheres;
            if (! empty($arrOption['domain_where']) && is_array($arrOption['domain_where'])) {
                $arrDomainWheres = $this->mergeWhere($arrOption['domain_where'], $arrDomainWheres);
            }

            // 主域名只有一个，路由可以有多个
            $strDomainBox = $arrDomain['router'] === false ? 'main' : 'rule';
            if (! isset($this->arrDomains[$strDomain])) {
                $this->arrDomains[$strDomain] = [];
            }
            $this->arrDomains[$strDomain]['domain_where'] = $arrDomainWheres;
            if (! isset($this->arrDomains[$strDomain][$strDomainBox])) {
                $this->arrDomains[$strDomain][$strDomainBox] = [];
            }

            // 纯域名绑定只支持一个，可以被覆盖
            if ($arrDomain['router'] === false) {
                $this->arrDomains[$strDomain][$strDomainBox] = $arrDomain;
            } else {
                // 优先插入
                if ($arrOption['prepend'] === true) {
                    array_unshift($this->arrDomains[$strDomain][$strDomainBox], $arrDomain);
                } else {
                    array_push($this->arrDomains[$strDomain][$strDomainBox], $arrDomain);
                }
            }
        }
    }

    /**
     * 注册分组路由
     *
     * @param array $arrOption
     * @sub string prefix 前缀
     * @sub string domain 域名
     * @sub array params 参数
     * @sub array where 参数正则
     * @sub boolean prepend 插入顺序
     * @sub boolean strict 严格模式，启用将在匹配正则 $
     * @param mixed $mixRouter
     * @return void
     */
    public function group(array $arrOption, $mixRouter)
    {
        if (! $this->checkExpired()) {
            return;
        }

        $this->arrGroupArgs = $arrOption = $this->mergeOption($this->arrGroupArgs, $arrOption);

        if ($mixRouter instanceof Closure) {
            call_user_func_array($mixRouter, []);
        } else {
            if (! is_array(current($mixRouter))) {
                $mixRouter = [
                    $mixRouter
                ];
            }
            foreach ($mixRouter as $arrVal) {
                if (! is_array($arrVal) || count($arrVal) < 2) {
                    continue;
                }

                if (! isset($arrVal[2])) {
                    $arrVal[2] = [];
                }

                $strPrefix = ! empty($arrArgs[2]['prefix']) ? $arrArgs[2]['prefix'] : (! empty($this->arrGroupArgs['prefix']) ? $this->arrGroupArgs['prefix'] : '');

                $this->import($strPrefix . $arrVal[0], $arrVal[1], $this->mergeOption($arrOption, $arrVal[2]));
            }
        }

        $this->arrGroupArgs = [];
    }

    /**
     * 导入路由配置数据
     *
     * @param array $arrData
     * @return void
     */
    public function importCache($arrData)
    {
        if (! $this->checkExpired()) {
            return;
        }

        if (isset($arrData['~domains~'])) {
            foreach ($arrData['~domains~'] as $arrVal) {
                if (is_array($arrVal) && isset($arrVal[1])) {
                    empty($arrVal[2]) && $arrVal[2] = [];
                    $this->domain($arrVal[0], $arrVal[1], $arrVal[2]);
                }
            }
            unset($arrData['~domains~']);
        }

        if ($arrData) {
            $this->import($arrData);
        }
    }

    /**
     * 获取绑定资源
     *
     * @param string $sBindName
     * @return mixed
     */
    public function getBind($sBindName)
    {
        return isset($this->arrBinds[$sBindName]) ? $this->arrBinds[$sBindName] : null;
    }

    /**
     * 判断是否绑定资源
     *
     * @param string $sBindName
     * @return boolean
     */
    public function hasBind($sBindName)
    {
        return isset($this->arrBinds[$sBindName]);
    }

    /**
     * 注册绑定资源
     *
     * @param mixed $mixBind
     * @param string $sController
     * @param string $sAction
     * @param string $sApp
     * @return mixed|void
     */
    public function bind($mixBindName = null, $mixBind = null)
    {
        $sController = $sAction = $sApp = null;
        if ($mixBindName) {
            list($sController, $sAction, $sApp) = $this->parseNode($mixBindName);
        }
        $sBindName = $this->packageNode($sController, $sAction, $sApp);

        if (is_null($mixBind)) {
            return $this->arrBinds[$sBindName] = $this->parseDefaultBind($sController, $sAction, $sApp);
        }

        if (! is_null($sAction)) {
            return $this->arrBinds[$sBindName] = $mixBind;
        } else {
            ! $sAction = $sAction = $this->action();

            switch (true) {

                // 判断是否为回调
                case is_callable($mixBind):
                    return $this->arrBinds[$sBindName] = $mixBind;
                    break;

                // 实例回调
                case is_object($mixBind):

                // 静态类回调
                case is_string($mixBind) && is_callable([
                    $mixBind,
                    $sAction
                ]):
                    return $this->arrBinds[$sBindName] = [
                        $mixBind,
                        $sAction
                    ];
                    break;

                // 数组支持,方法名即数组的键值,注册方法
                case is_array($mixBind):
                    if (isset($mixBind[$sAction])) {
                        return $this->arrBinds[$sBindName] = $mixBind[$sAction];
                    } else {
                        $this->nodeNotRegistered($sController, $sAction);
                    }
                    break;

                // 简单数据直接输出
                case is_scalar($mixBind):
                    return $this->arrBinds[$sBindName] = $mixBind;
                    break;

                default:
                    throw new InvalidArgumentException('The type of registered controller is not supported.');
                    break;
            }
        }
    }

    /**
     * 执行绑定
     *
     * @param string $sController
     * @param string $sAction
     * @param string $sApp
     * @return mixed|void
     */
    public function doBind($sController = null, $sAction = null, $sApp = null)
    {
        if (is_null($sController)) {
            $sController = $this->controller();
        }

        if (is_null($sAction)) {
            $sAction = $this->action();
        }

        if (is_null($sApp)) {
            $sApp = $this->app();
        }

        if (! ($mixAction = $this->getBind($this->packageNode($sController, $sAction, $sApp))) && ! ($mixAction = $this->bind($this->packageNode($sController, $sAction, $sApp)))) {
            $this->nodeNotRegistered($sController, $sAction);
        }

        switch (true) {

            // 判断是否为回调
            case is_callable($mixAction):
                return $this->objContainer->call($mixAction, $this->arrVariable);
                break;

            // 数组支持,方法名即数组的键值,注册方法
            case is_array($mixAction): 
                return $mixAction;
                break;

            // 简单数据直接输出
            case is_scalar($mixAction):
                return $mixAction;
                break;

            default:
                throw new InvalidArgumentException(sprintf('The registration method type %s is not supported.', $sAction));
                break;
        }
    }

    /**
     * 获取绑定的中间件
     *
     * @param string $sNode
     * @return mixed
     */
    public function getMiddleware($sNode)
    {
        $arrMiddleware = [];
        foreach ($this->arrMiddlewares as $sKey => $arrValue) {
            $sKey = $this->prepareRegexForWildcard($sKey, $this->getOption('middleware_strict'));
            if (preg_match($sKey, $sNode, $arrRes)) {
                $arrMiddleware = array_merge($arrMiddleware, $arrValue);
            }
        }
        return $arrMiddleware;
    }

    /**
     * 注册绑定中间件
     *
     * @param string $sMiddlewareName
     * @param string|array $mixMiddleware
     * @return void
     */
    public function middleware($sMiddlewareName, $mixMiddleware)
    {
        if (! $this->checkExpired()) {
            return;
        }

        if (! $mixMiddleware) {
            throw new InvalidArgumentException(sprintf('Middleware %s disallowed empty.', $sMiddlewareName));
        }

        if (! isset($this->arrMiddlewares[$sMiddlewareName])) {
            $this->arrMiddlewares[$sMiddlewareName] = [];
        }

        $this->arrMiddlewares[$sMiddlewareName] = array_merge($this->arrMiddlewares[$sMiddlewareName], $this->parseMiddlewares($mixMiddleware));
    }

    /**
     * 批量注册绑定中间件
     *
     * @param array $arrMiddleware
     * @return void
     */
    public function middlewares(array $arrMiddleware)
    {
        if (! $this->checkExpired()) {
            return;
        }

        foreach ($arrMiddleware as $sMiddlewareName => $mixMiddleware) {
            $this->middleware($sMiddlewareName, $mixMiddleware);
        }
    }

    /**
     * 获取绑定的 HTTP 方法
     *
     * @param string $sNode
     * @return mixed
     */
    public function getMethod($sNode)
    {
        if (array_key_exists($sNode, $this->arrMethods)) {
            return $this->arrMethods[$sNode];
        }

        $arrMethod = [];
        foreach ($this->arrMethods as $sKey => $arrValue) {
            $sKey = $this->prepareRegexForWildcard($sKey, $this->getOption('method_strict'));
            if (preg_match($sKey, $sNode, $arrRes)) {
                if ($arrMethod) {
                    $arrMethod = array_intersect($arrMethod, $arrValue);
                } else {
                    $arrMethod = $arrValue;
                }
            }
        }
        return $arrMethod;
    }

    /**
     * 注册绑定 HTTP 方法
     *
     * @param string $sMethodName
     * @param string|array $mixMethod
     * @return void
     */
    public function method($sMethodName, $mixMethod)
    {
        if (! $this->checkExpired()) {
            return;
        }

        if (! $mixMethod) {
            throw new InvalidArgumentException(sprintf('Method %s disallowed empty', $sMethodName));
        }

        if (! isset($this->arrMethods[$sMethodName])) {
            $this->arrMethods[$sMethodName] = [];
        }

        $mixMethod = ( array ) $mixMethod;

        $mixMethod = array_map(function ($strItem) {
            return strtoupper($strItem);
        }, $mixMethod);

        if (is_array($mixMethod)) {
            $this->arrMethods[$sMethodName] = array_merge($this->arrMethods[$sMethodName], $mixMethod);
        } else {
            $this->arrMethods[$sMethodName][] = $mixMethod;
        }
    }

    /**
     * 批量注册绑定 HTTP 方法
     *
     * @param array $arrMethod
     * @return void
     */
    public function methods($arrMethod)
    {
        if (! $this->checkExpired()) {
            return;
        }

        foreach ($arrMethod as $sMethod => $mixMethod) {
            $this->method($sMethod, $mixMethod);
        }
    }

    /**
     * 设置路由缓存地址
     *
     * @param string $strCachePath
     * @return $this
     */
    public function cachePath($strCachePath)
    {
        $this->strCachePath = $strCachePath;
        return $this;
    }

    /**
     * 设置 development
     *
     * @param boolean $booDevelopment
     * @return $this
     */
    public function development($booDevelopment)
    {
        $this->booDevelopment = $booDevelopment;
        return $this;
    }

    /**
     * 检查路由缓存是否过期
     *
     * @return boolean
     */
    public function checkExpired()
    {
        return $this->booDevelopment === true || ! $this->checkOpen() || ! is_file($this->strCachePath);
    }

    /**
     * 添加匹配变量
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addVariable($name, $value)
    {
        $this->arrVariable[$name] = $value;
    }

    /**
     * 当前域名
     *
     * @return array
     */
    public function getDomains() {
        return $this->arrDomains;
    }

    /**
     * 取得当前路由
     *
     * @return array
     */
    public function getRouters() {
        return $this->arrRouters;
    }

    /**
     * 域名匹配成功后的路由规则
     *
     * @return array
     */
    public function getDomainRouters() {
        return $this->arrDomainRouters;
    }

    /**
     * 域名匹配成功后的路由规则
     *
     * @param array $domainRouters
     * @return void
     */
    public function setDomainRouters(array $domainRouters) {
        $this->arrDomainRouters = $domainRouters;
    }

    /**
     * 域名路由匹配数据
     *
     * @return array
     */
    public function getDomainData() {
        return $this->arrDomainData;
    }

    /**
     * 添加域名匹配数据
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addDomainData($name, $value)
    {
        $this->arrDomainData[$name] = $value;
    }

    /**
     * 格式化正则
     *
     * @param string $sRegex
     * @return string
     */
    public function formatRegex($sRegex)
    {
        $sRegex = $this->escapeRegexCharacter($sRegex);

        // 还原变量特殊标记
        return str_replace([
            '\{',
            '\}'
        ], [
            '{',
            '}'
        ], $sRegex);
    }

    /**
     * 分析 url 数据
     * like [home://blog/index?arg1=1&arg2=2]
     *
     * @param string $sUrl
     * @return array
     */
    public function parseNodeUrl($sUrl)
    {
        $arrData = [];

        // 解析 url
        if (strpos($sUrl, '://') === false) {
            $sUrl = 'QueryPHP://' . $sUrl;
        }
        $sUrl = parse_url($sUrl);

        // 应用
        if ($sUrl['scheme'] != 'QueryPHP') {
            $arrData[static::APP] = $sUrl['scheme'];
        }

        // 控制器
        $arrData[static::CONTROLLER] = $sUrl['host'];

        // 方法
        if (isset($sUrl['path']) && $sUrl['path'] != '/') {
            $arrData[static::ACTION] = ltrim($sUrl['path'], '/');
        }

        // 额外参数
        if (isset($sUrl['query'])) {
            foreach (explode('&', $sUrl['query']) as $strQuery) {
                $strQuery = explode('=', $strQuery);
                $arrData[$strQuery[0]] = $strQuery[1];
            }
        }

        return $arrData;
    }

    /**
     * 检查路由缓存是否开启
     *
     * @return boolean
     */
    public function checkOpen()
    {
        return $this->getOption('router_cache') && $this->strCachePath;
    }

    /**
     * 初始化请求
     *
     * @return void
     */
    public function initRequest()
    {
        $this->strApp = null;
        $this->strController = null;
        $this->strAction = null;
    }    

    /**
     * 路由匹配
     *
     * @return void
     */
    protected function matchRouter()
    {
        $matches = $this->getRouterMatches();

        foreach ($matches as $item) {
            $item = 'Queryyetsimple\Router\Match\\' . $item;
            $data = (new $item)->matche($this, $this->objRequest);

            if ($data) {
                $this->matcheData = $data;
                break;
            }
        }

        $this->completeRequest();
    }

    /**
     * 获取路由匹配项
     *
     * @return array
     */
    protected function getRouterMatches()
    {
        if ($this->objRequest->isCli()) {
            $matches = ['Cli'];
        } else {
            if ($this->getOption('model') == 'pathinfo') {
                $this->readCache();
                
                $matches = ['Domain', 'Url', 'PathInfo'];
            } else {
                $matches = ['Defaults'];
            }
        }

        return $matches;
    }

    /**
     * 验证 HTTP 方法
     *
     * @return void
     */
    protected function validateMethod()
    {
        $method = $this->objRequest->getMethod();

        $arrMethod = $this->getMethod($this->packageNode());
        if ($arrMethod && ! in_array($method, $arrMethod)) {
            throw new RuntimeException(sprintf('The node is allowed http method %s, but your current http method is %s', implode(',', $arrMethod), $method));
        }
    }

    /**
     * 路由缓存
     *
     * @return void
     */
    protected function readCache()
    {
        if (! $this->checkOpen()) {
            return;
        }

        if ($this->booDevelopment === false && is_file($this->strCachePath)) {
            $arrCacheData = ( array ) include $this->strCachePath;
            $this->arrDomains = $arrCacheData['domains'];
            $this->arrRouters = $arrCacheData['routers'];
            $this->arrDomainWheres = $arrCacheData['domain_wheres'];
            $this->arrWheres = $arrCacheData['wheres'];
            $this->arrMiddlewares = $arrCacheData['middlewares'];
            $this->arrMethods = $arrCacheData['methods'];
            unset($arrCacheData);
            return;
        }

        $arrCacheData = [
            'domains' => $this->arrDomains,
            'routers' => $this->arrRouters,
            'domain_wheres' => $this->arrDomainWheres,
            'wheres' => $this->arrWheres,
            'middlewares' => $this->arrMiddlewares,
            'methods' => $this->arrMethods
        ];

        if (! is_dir(dirname($this->strCachePath))) {
            mkdir(dirname($this->strCachePath), 0777, true);
        }

        if (! file_put_contents($this->strCachePath, '<?' . 'php /* ' . date('Y-m-d H:i:s') . ' */ ?' . '>' . PHP_EOL . '<?' . 'php return ' . var_export($arrCacheData, true) . '; ?' . '>')) {
            throw new RuntimeException(sprintf('Dir %s do not have permission.', $this->strCachePath));
        }

        chmod($this->strCachePath, 0777);

        unset($arrCacheData);
    }

    /**
     * 通配符正则
     *
     * @param string $strFoo
     * @param bool $booStrict
     * @return string
     */
    protected function prepareRegexForWildcard($strRegex, $booStrict = true)
    {
        return '/^' . str_replace('6084fef57e91a6ecb13fff498f9275a7', '(\S+)', $this->escapeRegexCharacter(str_replace('*', '6084fef57e91a6ecb13fff498f9275a7', $strRegex))) . ($booStrict ? '$' : '') . '/';
    }

    /**
     * 转移正则表达式特殊字符
     *
     * @param string $sTxt
     * @return string
     */
    protected function escapeRegexCharacter($sTxt)
    {
        $sTxt = str_replace([
            '$',
            '/',
            '?',
            '*',
            '.',
            '!',
            '-',
            '+',
            '(',
            ')',
            '[',
            ']',
            ',',
            '{',
            '}',
            '|'
        ], [
            '\$',
            '\/',
            '\\?',
            '\\*',
            '\\.',
            '\\!',
            '\\-',
            '\\+',
            '\\(',
            '\\)',
            '\\[',
            '\\]',
            '\\,',
            '\\{',
            '\\}',
            '\\|'
        ], $sTxt);

        return $sTxt;
    }

    /**
     * 节点资源未注册异常
     *
     * @param string $sController
     * @param string $sAction
     * @return void
     */
    protected function nodeNotRegistered($sController, $sAction)
    {
        $message = sprintf('The node %s is not registered.', $this->makeNode($sController, $sAction));

        throw new InvalidArgumentException($message);
    }

    /**
     * 生成节点资源
     *
     * @param string $sController
     * @param string $sAction
     * @return string
     */
    protected function makeNode($sController, $sAction)
    {
        return $this->app() . '\\' . $this->controllerDir() . '\\' . $sController . '->' . $sAction . '()';
    }

    /**
     * 取得控制器命名空间目录
     *
     * @return string
     */
    protected function controllerDir()
    {
        $result = $this->getOption('controller_dir');

        if ($this->prefix()) {
            $result = $result . '\\' . $this->prefix();
        }

        return $result;
    }

    /**
     * 合并 option 参数
     *
     * @param array $arrOption
     * @param array $arrExtend
     * @return array
     */
    protected function mergeOption(array $arrOption, array $arrExtend)
    {
        // 合并特殊参数
        foreach ([
            'params',
            'where',
            'domain_where'
        ] as $strType) {
            if (! empty($arrExtend[$strType]) && is_array($arrExtend[$strType])) {
                if (! isset($arrOption[$strType])) {
                    $arrOption[$strType] = [];
                }
                $arrOption[$strType] = $this->mergeWhere($arrOption[$strType], $arrExtend[$strType]);
            }
        }

        // 合并额外参数
        foreach ([
            'prefix',
            'domain',
            'prepend',
            'strict',
            'router'
        ] as $strType) {
            if (isset($arrExtend[$strType])) {
                $arrOption[$strType] = $arrExtend[$strType];
            }
        }

        return $arrOption;
    }

    /**
     * 合并 where 正则参数
     *
     * @param array $arrWhere
     * @param array $arrExtend
     * @return array
     */
    protected function mergeWhere(array $arrWhere, array $arrExtend)
    {
        // 合并参数正则
        if (! empty($arrExtend) && is_array($arrExtend)) {
            if (is_string(key($arrExtend))) {
                $arrWhere = array_merge($arrWhere, $arrExtend);
            } else {
                $arrWhere[$arrExtend[0]] = $arrExtend[1];
            }
        }

        return $arrWhere;
    }

    /**
     * 完成请求
     *
     * @return void
     */
    protected function completeRequest()
    {
        if ($this->getOption('pathinfo_restful')) {
            $this->pathinfoRestful();
        }

        foreach ([
            'app',
            'controller',
            'action'
        ] as $strType) {
            $this->objRequest->{'set' . ucfirst($strType)}($this->{$strType}());
        }

        $this->objRequest->params->replace($this->params());
    }

    /**
     * 智能 restful 解析
     * 路由匹配失败后尝试智能化解析
     *
     * @return void
     */
    protected function pathinfoRestful()
    {
        if (isset($this->matcheData[static::ACTION])) {
            return;
        }

        switch ($this->objRequest->getMethod()) {
            case 'GET':
                if (! empty($this->matcheData[static::PARAMS])) {
                    $this->matcheData[static::ACTION] = static::RESTFUL_SHOW;
                }
                break;
            case 'POST':
                $this->matcheData[static::ACTION] = static::RESTFUL_STORE;
                break;
            case 'PUT':
                $this->matcheData[static::ACTION] = static::RESTFUL_UPDATE;
                break;
            case 'DELETE':
                $this->matcheData[static::ACTION] = static::RESTFUL_DESTROY;
                break;
        }
    }

    /**
     * 分析默认绑定
     *
     * @param string $sController
     * @param string $sAction
     * @param string $sApp
     * @return false|callable
     */
    protected function parseDefaultBind($sController = null, $sAction = null, $sApp = null)
    {
        if (is_null($sController)) {
            $sController = $this->controller();
        }

        if (is_null($sAction)) {
            $sAction = $this->action();
        }

        if (is_null($sApp)) {
            $sApp = $this->app();
        }

        // 尝试直接读取方法控制器类
        $sControllerClass = $sApp . '\\' . $this->controllerDir() . '\\' . $sController . '\\' . $sAction;
        if (class_exists($sControllerClass)) {
            $controller = $this->objContainer->make($sControllerClass, $this->arrVariable);
            $method = method_exists($controller, 'handle') ? 'handle' : 'run';
        } else {

            // 尝试读取默认控制器
            $sControllerClass = $sApp . '\\' . $this->controllerDir() . '\\' . $sController;
            if (! class_exists($sControllerClass)) {
                return false;
            }

            $controller = $this->objContainer->make($sControllerClass, $this->arrVariable);
            $method = $sAction;
        }

        if ($controller instanceof IController) {
            $controller->setView($this->objContainer['view']);
        }

        if (! method_exists($controller, $method)) {
            $this->nodeNotRegistered($sController, $sAction);
        }

        return [
            $controller,
            $method
        ];
    }

    /**
     * 取得打包节点
     *
     * @param string $strApp
     * @param string $strController
     * @param string $strAction
     * @return string
     */
    protected function packageNode($strController = null, $strAction = null, $strApp = null)
    {
        return ($strApp ?  : $this->app()) . '://' . ($strController ?  : $this->controller()) . '/' . ($strAction ?  : $this->action());
    }

    /**
     * 分析节点
     *
     * @param string $strApp
     * @return arrat
     */
    protected function parseNode($strNode)
    {
        $sController = $sAction = $sApp = null;
        $arrTemp = $this->parseNodeUrl($strNode);

        if (! empty($arrTemp[static::APP]) && $arrTemp[static::APP] != '*') {
            $sApp = $arrTemp[static::APP];
        }
        if (! empty($arrTemp[static::CONTROLLER]) && $arrTemp[static::CONTROLLER] != '*') {
            $sController = $arrTemp[static::CONTROLLER];
        }
        if (! empty($arrTemp[static::ACTION]) && $arrTemp[static::ACTION] != '*') {
            $sAction = $arrTemp[static::ACTION];
        }

        unset($arrTemp);

        return [
            $sController ?  : $this->controller(),
            $sAction ?  : $this->action(),
            $sApp ?  : $this->app()
        ];
    }

    /**
     * 解析中间件
     *
     * @param string|array $mixMiddleware
     * @return array
     */
    protected function parseMiddlewares($mixMiddleware)
    {
        $arrMiddleware = [];
        foreach (( array ) $mixMiddleware as $strTemp) {
            if (! is_string($strTemp)) {
                throw new InvalidArgumentException('Middleware only allowed string.');
            }

            $strParams = '';
            if (strpos($strTemp, ':') !== false) {
                list($strTemp, $strParams) = explode(':', $strTemp);
            }

            if (isset($this->getOption('middleware_group')[$strTemp])) {
                foreach (( array ) $this->getOption('middleware_group')[$strTemp] as $strTempTwo) {
                    $strParams = '';
                    if (strpos($strTempTwo, ':') !== false) {
                        list($strTempTwo, $strParams) = explode(':', $strTempTwo);
                    }

                    if (isset($this->getOption('middleware_alias')[$strTempTwo])) {
                        $arrMiddleware[] = $this->explodeMiddlewareName($this->getOption('middleware_alias')[$strTempTwo], $strParams);
                    } else {
                        $arrMiddleware[] = $this->explodeMiddlewareName($strBackupTempTwo, $strParams);
                    }
                }
            } elseif (isset($this->getOption('middleware_alias')[$strTemp])) {
                $arrMiddleware[] = $this->explodeMiddlewareName($this->getOption('middleware_alias')[$strTemp], $strParams);
            } else {
                $arrMiddleware[] = $this->explodeMiddlewareName($strTemp, $strParams);
            }
        }

        return $arrMiddleware;
    }

    /**
     * 中间件名字
     *
     * @param string $strMiddleware
     * @param string $strParams
     * @return string
     */
    protected function explodeMiddlewareName($strMiddleware, $strParams)
    {
        return $strMiddleware . ($strParams ? ':' . $strParams : '');
    }
}