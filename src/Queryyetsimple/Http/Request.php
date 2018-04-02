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
namespace Queryyetsimple\Http;

use ArrayAccess;
use SplFileObject;
use RuntimeException;
use Queryyetsimple\{
    Option\TClass,
    Support\TMacro,
    Support\IArray
};

/**
 * HTTP 请求
 * This class borrows heavily from the Symfony2 Framework and is part of the symfony package
 * 
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2016.11.19
 * @version 1.0
 * @see Symfony\Component\HttpFoundation (https://github.com/symfony/symfony)
 */
class Request implements IRequest, IArray, ArrayAccess
{
    use TClass;

    use TMacro;

    /**
     * GET Bag
     *
     * @var \Queryyetsimple\Http\Bag
     */
    public $query;

    /**
     * POST Bag
     *
     * @var \Queryyetsimple\Http\Bag
     */
    public $request;

    /**
     * 路由解析后的参数
     *
     * @var \Queryyetsimple\Http\Bag
     */
    public $params;

    /**
     * COOKIE Bag
     *
     * @var \Queryyetsimple\Http\Bag
     */
    public $cookies;

    /**
     * FILE Bag
     *
     * @var \Queryyetsimple\Http\FileBag
     */
    public $files;

    /**
     * SERVER Bag
     *
     * @var \Queryyetsimple\Http\ServerBag
     */
    public $server;

    /**
     * HEADER Bag
     *
     * @var \Queryyetsimple\Http\HeaderBag
     */
    public $headers;

    /**
     * 内容
     * 
     * @var string|resource|false|null
     */
    protected $content;

    /**
     * 基础 url
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * 基础路径
     * 
     * @var string
     */
    protected $basePath;

    /**
     * 请求 url
     *
     * @var string
     */
    protected $requestUri;

    /**
     * 请求类型
     *
     * @var string
     */
    protected $method;

    /**
     * public URL
     *
     * @var string
     */
    protected $publicUrl;

    /**
     * pathInfo
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * 应用名字
     *
     * @var string
     */
    protected $app;

    /**
     * 控制器名字
     *
     * @var string
     */
    protected $controller;

    /**
     * 方法名字
     *
     * @var string
     */
    protected $action;

    /**
     * 当前语言
     *
     * @var string
     */
    protected $language;

    /**
     * 配置
     *
     * @var array
     */
    protected $option = [
        'var_method' => '_method',
        'var_ajax' => '_ajax',
        'var_pjax' => '_pjax',
        'html_suffix' => '.html',
        'rewrite' => false,
        'public' => 'http://public.foo.bar'
    ];

    /**
     * 服务器 url 重写支持 pathInfo
     *
     * Nginx
     * location @rewrite {
     *     rewrite ^/(.*)$ /index.php?_url=/$1;
     * }
     *
     * @var string
     */
    const PATHINFO_URL = '_url';

    /**
     * 构造函数
     * 
     * @param array $query
     * @param array $request
     * @param array $params
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @param array $option
     * @return void
     */
    public function __construct(array $query = [], array $request = [], array $params = [], array $cookies = [], array $files = [], array $server = [], $content = null, array $option = [])
    {
        $this->reset($query, $request, $params, $cookies, $files, $server, $content);
        $this->options($option);
    }

    /**
     * 重置或者初始化
     * 
     * @param array $query
     * @param array $request
     * @param array $params
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string $content
     * @param array $option
     * @return void
     */
    public function reset(array $query = [], array $request = [], array $params = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->query = new Bag($query);
        $this->request = new Bag($request);
        $this->params = new Bag($params);
        $this->cookies = new Bag($cookies);
        $this->files = new FileBag($files);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());

        $this->content = $content;
        $this->baseUrl = null;
        $this->requestUri = null;
        $this->method = null;
        $this->publicUrl = null;
        $this->pathInfo = null;
        $this->app = null;
        $this->controller = null;
        $this->action = null;
        $this->language = null;
    }

    /**
     * 全局变量创建一个 Request
     *
     * @param array $options
     * @return static
     */
    public static function createFromGlobals(array $option = [])
    {
        $request = new static($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, null, $option);

        $request = static::normalizeRequestFromContent($request);

        return $request;
    }

    /**
     * 格式化请求的内容
     *
     * @param \Queryyetsimple\Http\Request $request
     * @return \Queryyetsimple\Http\Request
     */
    public static function normalizeRequestFromContent(Request $request)
    {
        $contentType = $request->headers->get("CONTENT_TYPE");
        $method = strtoupper($request->server->get("REQUEST_METHOD", self::METHOD_GET));

        if ($contentType && 0 === strpos($contentType, 'application/x-www-form-urlencoded') && 
            in_array($method, [
                static::METHOD_PUT, 
                static::METHOD_DELETE, 
                static::METHOD_PATCH
            ])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new Bag($data);
        }

        return $request;
    }

    /**
     * 获取参数
     *
     * @param string $key
     * @param mixed $defaults
     * @return mixed
     */
    public function get($key, $defaults = null)
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        } else {
            return $this->params->get($key, $defaults);
        }
    }

    /**
     * 请求是否包含给定的 key
     *
     * @param string|array $key
     * @return bool
     */
    public function exists($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        $input = $this->all();

        foreach ($keys as $value) {
            if (! array_key_exists($value, $input)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 请求是否包含非空
     *
     * @param string|array $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 取得给定的 key 数据
     *
     * @param array|mixed $keys
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = [];

        $input = $this->all();

        foreach ($keys as $key) {
            $results[$key] = $input[$key] ?? null;
        }

        return $results;
    }

    /**
     * 取得排除给定的 key 数据
     *
     * @param array|mixed $keys
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $results = $this->all();

        foreach ($keys as $key) {
            if (array_key_exists($key, $results)) {
                unset($results[$key]);
            }
        }

        return $results;
    }

    /**
     * 取回输入和文件
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->input(), $this->allFiles());
    }

    /**
     * 获取输入数据
     *
     * @param string $key
     * @param string|array|null $defaults
     * @return mixed
     */
    public function input($key = null, $defaults = null)
    {
        $input = $this->getInputSource()->all() + $this->query->all();

        if (is_null($key)) {
            return $input;
        }

        return $input[$key] ?? $defaults;
    }

    /**
     * 取回 query
     *
     * @param string $key
     * @param string|array|null $defaults
     * @return string|array
     */
    public function query($key = null, $defaults = null)
    {
        return $this->getItem('query', $key, $defaults);
    }

    /**
     * 请求是否存在 COOKIE
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCookie($key)
    {
        return ! is_null($this->cookie($key));
    }

    /**
     * 取回 cookie
     *
     * @param string $key
     * @param string|array|null $defaults
     * @return string|array
     */
    public function cookie($key = null, $defaults = null)
    {
        return $this->getItem('cookies', $key, $defaults);
    }

    /**
     * 取得所有文件
     *
     * @return array
     */
    public function allFiles()
    {
        return $this->files->all();
    }

    /**
     * 获取文件
     * 数组文件请在末尾加上反斜杆访问
     *
     * @param string $key
     * @param mixed $defaults
     * @return \Queryyetsimple\Http\UploadedFile|array|null
     */
    public function file($key = null, $defaults = null)
    {
        if (strpos($key, '\\') === false) {
            return $this->getItem('files', $key, $defaults);
        } else {
            return $this->files->getArr($key, $defaults);
        }
    }

    /**
     * 文件是否存在已上传的文件
     * 数组文件请在末尾加上反斜杆访问
     *
     * @param string $key
     * @return bool
     */
    public function hasFile($key)
    {
        $files = $this->file($key);

        if (! is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($this->isValidFile($file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 验证是否为文件实例
     *
     * @param mixed $file
     * @return bool
     */
    public function isValidFile($file)
    {
        return $file instanceof SplFileObject && $file->getPath() != '';
    }

    /**
     * 取回 header
     *
     * @param string $key
     * @param string|array|null $defaults
     * @return string|array
     */
    public function header($key = null, $defaults = null)
    {
        return $this->getItem('headers', $key, $defaults);
    }

    /**
     * 取回 server
     *
     * @param string $key
     * @param string|array|null $defaults
     * @return string|array
     */
    public function server($key = null, $defaults = null)
    {
        return $this->getItem('server', $key, $defaults);
    }

    /**
     * 取回数据项
     *
     * @param string $source
     * @param string $key
     * @param string|array|null $defaults
     * @return string|array
     */
    public function getItem($source, $key, $defaults)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        return $this->$source->get($key, $defaults);
    }

    /**
     * 合并输入
     *
     * @param array $input
     * @return void
     */
    public function merge(array $input)
    {
        $this->getInputSource()->add($input);
    }

    /**
     * 替换输入
     *
     * @param array $input
     * @return void
     */
    public function replace(array $input)
    {
        $this->getInputSource()->replace($input);
    }

    /**
     * PHP 运行模式命令行, 兼容 swoole http service
     * Swoole http 服务器也以命令行运行
     * 
     * @link http://php.net/manual/zh/function.php-sapi-name.php
     * @return boolean
     */
    public function isCli()
    {
        if($this->server->get('SERVER_SOFTWARE') == 'swoole-http-server') {
            return false;
        }

        return $this->isRealCli();
    }

    /**
     * PHP 运行模式命令行
     * 
     * @link http://php.net/manual/zh/function.php-sapi-name.php
     * @return boolean
     */
    public function isRealCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * PHP 运行模式 cgi
     *
     * @link http://php.net/manual/zh/function.php-sapi-name.php
     * @return boolean
     */
    public function isCgi()
    {
        return substr(PHP_SAPI, 0, 3) == 'cgi';
    }

    /**
     * 是否为 Ajax 请求行为
     *
     * @return boolean
     */
    public function isAjax()
    {
        $field = $this->getOption('var_ajax');

        if ($this->request->has($field) || $this->query->has($field)) {
            return true;
        }

        return $this->isRealAjax();
    }

    /**
     * 是否为 Ajax 请求行为真实
     *
     * @return boolean
     */
    public function isRealAjax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * 是否为 Ajax 请求行为真实
     *
     * @return boolean
     */
    public function isXmlHttpRequest()
    {
        return $this->headers->get('X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * 是否为 Pjax 请求行为
     *
     * @return boolean
     */
    public function isPjax()
    {
        $field = $this->getOption('var_pjax');

        if ($this->request->has($field) || $this->query->has($field)) {
            return true;
        }

        return $this->isRealPjax();
    }

    /**
     * 是否为 Pjax 请求行为真实
     *
     * @return boolean
     */
    public function isRealPjax()
    {
        return ! is_null($this->headers->get('X_PJAX'));
    }

    /**
     * 是否为手机访问
     *
     * @return boolean
     */
    public function isMobile()
    {
        $useAgent = $this->headers->get('USER_AGENT');
        $allHttp = $this->server->get('ALL_HTTP');

        // Pre-final check to reset everything if the user is on Windows
        if (strpos($useAgent, 'windows') !== false) {
            return false;
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', $useAgent)) {
            return true;
        }

        if (strpos($this->headers->get('ACCEPT'), 'application/vnd.wap.xhtml+xml') !== false) {
            return true;
        }

        if ($this->headers->has('X_WAP_PROFILE') || $this->headers->has('PROFILE')) {
            return true;
        }

        if (in_array(strtolower(substr($useAgent, 0, 4)), [
            'w3c ',
            'acs-',
            'alav',
            'alca',
            'amoi',
            'audi',
            'avan',
            'benq',
            'bird',
            'blac',
            'blaz',
            'brew',
            'cell',
            'cldc',
            'cmd-',
            'dang',
            'doco',
            'eric',
            'hipt',
            'inno',
            'ipaq',
            'java',
            'jigs',
            'kddi',
            'keji',
            'leno',
            'lg-c',
            'lg-d',
            'lg-g',
            'lge-',
            'maui',
            'maxo',
            'midp',
            'mits',
            'mmef',
            'mobi',
            'mot-',
            'moto',
            'mwbp',
            'nec-',
            'newt',
            'noki',
            'oper',
            'palm',
            'pana',
            'pant',
            'phil',
            'play',
            'port',
            'prox',
            'qwap',
            'sage',
            'sams',
            'sany',
            'sch-',
            'sec-',
            'send',
            'seri',
            'sgh-',
            'shar',
            'sie-',
            'siem',
            'smal',
            'smar',
            'sony',
            'sph-',
            'symb',
            't-mo',
            'teli',
            'tim-',
            'tosh',
            'tsm-',
            'upg1',
            'upsi',
            'vk-v',
            'voda',
            'wap-',
            'wapa',
            'wapi',
            'wapp',
            'wapr',
            'webc',
            'winw',
            'winw',
            'xda',
            'xda-'
        ])) {
            return true;
        }

        if (strpos(strtolower($allHttp), 'operamini') !== false) {
            return true;
        }

        // But WP7 is also Windows, with a slightly different characteristic
        if (strpos($useAgent, 'windows phone') !== false) {
            return true;
        }

        return false;
    }

    /**
     * 是否为 HEAD 请求行为
     *
     * @return boolean
     */
    public function isHead()
    {
        return $this->getMethod() == static::METHOD_HEAD;
    }

    /**
     * 是否为 GET 请求行为
     *
     * @return boolean
     */
    public function isGet()
    {
        return $this->getMethod() == static::METHOD_GET;
    }

    /**
     * 是否为 POST 请求行为
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->getMethod() == static::METHOD_POST;
    }

    /**
     * 是否为 PUT 请求行为
     *
     * @return boolean
     */
    public function isPut()
    {
        return $this->getMethod() == static::METHOD_PUT;
    }

    /**
     * 是否为 PATCH 请求行为
     *
     * @return boolean
     */
    public function isPatch()
    {
        return $this->getMethod() == static::METHOD_PATCH;
    }

    /**
     * 是否为 PURGE 请求行为
     *
     * @return boolean
     */
    public function isPurge()
    {
        return $this->getMethod() == static::METHOD_PURGE;
    }

    /**
     * 是否为 OPTIONS 请求行为
     *
     * @return boolean
     */
    public function isOptions()
    {
        return $this->getMethod() == static::METHOD_OPTIONS;
    }

    /**
     * 是否为 TRACE 请求行为
     *
     * @return boolean
     */
    public function isTrace()
    {
        return $this->getMethod() == static::METHOD_TRACE;
    }

    /**
     * 是否为 CONNECT 请求行为
     *
     * @return boolean
     */
    public function isConnect()
    {
        return $this->getMethod() == static::METHOD_CONNECT;
    }

    /**
     * 获取 IP 地址
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->headers->get('CLIENT_IP', $this->server->get('REMOTE_ADDR', '0.0.0.0'));
    }

    /**
     * 请求类型
     *
     * @return string
     */
    public function getMethod()
    {
        if (! is_null($this->method)) {
            return $this->method;
        }

        $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

        if ('POST' === $this->method) {
            if ($method = $this->headers->get('X-HTTP-METHOD-OVERRIDE')) {
                $this->method = strtoupper($method);
            } else {
                $field = $this->getOption('var_method');

                $this->method = strtoupper($this->request->get($field, $this->query->get($field, 'POST')));
            }
        }

        return $this->method;
    }

    /**
     * 设置请求类型
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', $method);

        return $this;
    }

    /**
     * 实际请求类型
     *
     * @return string
     */
    public function getRealMethod()
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }

    /**
     * 验证是否为指定的方法
     *
     * @param string $method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * 取回应用名
     *
     * @return string
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * 取回控制器名
     *
     * @return string
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * 取回方法名
     *
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * 取得节点
     *
     * @return string
     */
    public function getNode()
    {
        return $this->app() . '://' . $this->controller() . '/' . $this->action();
    }

    /**
     * 设置应用名
     *
     * @param string $app
     * @return $this
     */
    public function setApp($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * 设置控制器名
     *
     * @param string $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * 设置方法名
     *
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * 返回当前的语言
     *
     * @return string|null
     */
    public function language()
    {
        return $this->language;
    }

    /**
     * 返回当前的语言
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * 设置当前的语言
     *
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        
        return $this;
    }

    /**
     * 取得请求内容
     *
     * @return string|resource
     */
    public function getContent()
    {
        $resources = is_resource($this->content);

        if ($resources) {
            rewind($this->content);
            return stream_get_contents($this->content);
        }

        if (null === $this->content || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * 返回网站公共文件目录
     *
     * @return string
     */
    public function getPublicUrl()
    {
        if (! is_null($this->publicUrl)) {
            return $this->publicUrl;
        }

        return $this->publicUrl = $this->getOption('public');
    }

    /**
     * 设置网站公共文件目录
     *
     * @param string $publicUrl
     * @return $this
     */
    public function setPublicUrl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * 返回 root URL
     *
     * @return string
     */
    public function getRoot()
    {
        return rtrim($this->getSchemeAndHttpHost() . $this->getBaseUrl(), '/');
    }

    /**
     * 返回入口文件
     *
     * @return string
     */
    public function getEnter()
    {
        if ($this->isCli()) {
            return '';
        }

        $scriptName = $this->getScriptName();

        if ($this->getOption('rewrite') !== true) {
            return $scriptName;
        }

        $scriptName = dirname($scriptName);
        if ($scriptName == '\\') {
            $scriptName = '/';
        }

        return $scriptName;
    }

    /**
     * 取得脚本名字
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }

    /**
     * 是否启用 https
     *
     * @return boolean
     */
    public function isSecure()
    {
        if (in_array($this->server->get('HTTPS'), ['1', 'on'])) {
            return true;
        } elseif ($this->server->get('SERVER_PORT') == '443') {
            return true;
        }

        return false;
    }

    /**
     * 取得 http host
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    /**
     * 获取 host
     *
     * @return string
     */
    public function getHost()
    {
        $host = $this->headers->get('X_FORWARDED_HOST', $this->headers->get('HOST', ''));

        if (! $host) {
            $host = $this->server->get('SERVER_NAME', $this->server->get('SERVER_ADDR', ''));
        }

        if (strpos($host, ':') !== false) {
            list($host) = explode(':', $host);
        }

        return $host;
    }

    /**
     * 取得 Scheme 和 Host
     *
     * @return string
     */
    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * 返回当前 URL 地址
     *
     * @return string
     */
    public function getUri()
    {
        if (null !== $queryString = $this->getQueryString()) {
            $queryString = '?' . $queryString;
        }

        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $queryString;
    }

    /**
     * 服务器端口
     *
     * @return integer
     */
    public function getPort()
    {
        $port = $this->server->get('SERVER_PORT');

        if (! $port) {
            $port = 'https' === $this->getScheme() ? 443 : 80;
        }

        return $port;
    }

    /**
     * 返回 scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * 取回查询参数
     *
     * @return string|null
     */
    public function getQueryString()
    {
        $queryString = $this->normalizeQueryString($this->server->get('QUERY_STRING'));

        return '' === $queryString ? null : $queryString;
    }

    /**
     * 设置 pathInfo
     *
     * @param string $pathInfo
     * @return $this
     */
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;

        return $this;
    }

    /**
     * pathInfo 兼容性分析
     *
     * @return string
     */
    public function getPathInfo()
    {
        if (! is_null($this->pathInfo)) {
            return $this->pathInfo;
        }

        $pathInfo = $this->server->get('PATH_INFO');
        if ($pathInfo) {
            return $this->pathInfo = $this->parsePathInfo($pathInfo);
        }

        // 服务器重写
        if ($this->query->get(static::PATHINFO_URL)) {
            $pathInfo = $this->parsePathInfo($this->query->get(static::PATHINFO_URL));
            $this->query->remove(static::PATHINFO_URL);
            return $this->pathInfo = $pathInfo;
        }

        // 分析基础 url
        $baseUrl = $this->getBaseUrl();

        // 分析请求参数
        if (null === ($requestUri = $this->getRequestUri())) {
            return $this->pathInfo = $this->parsePathInfo('');
        }

        if (($pos = strpos($requestUri, '?')) > -1) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $baseUrl) && (false === ($pathInfo = substr($requestUri, strlen($baseUrl))))) {
            $pathInfo = '';
        } elseif (null === $baseUrl) {
            $pathInfo = $requestUri;
        }

        return $this->pathInfo = $this->parsePathInfo($pathInfo);
    }

    /**
     * 获取基础路径
     *
     * @return string
     */
    public function getBasePath()
    {
        if (null !== $this->basePath) {
            return $this->basePath;
        }

        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }

        $filename = basename($this->server->get('SCRIPT_FILENAME'));

        if (basename($baseUrl) === $filename) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if ('\\' === DIRECTORY_SEPARATOR) {
            $basePath = str_replace('\\', '/', $basePath);
        }

        $this->basePath = rtrim($basePath, '/');

        return $this->basePath;
    }

    /**
     * 分析基础 url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (! is_null($this->baseUrl)) {
            return $this->baseUrl;
        }

        // 兼容分析
        $fileName = basename($this->server->get('SCRIPT_FILENAME'));

        if (basename($this->server->get('SCRIPT_NAME')) === $fileName) {
            $url = $this->server->get('SCRIPT_NAME');
        } elseif (basename($this->server->get('PHP_SELF')) === $fileName) {
            $url = $this->server->get('PHP_SELF');
        } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $fileName) {
            $url = $this->server->get('ORIG_SCRIPT_NAME');
        } else {
            $path = $this->server->get('PHP_SELF');
            $segs = explode('/', trim($fileName, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $maxCount = count($segs);

            $url = '';
            do {
                $seg = $segs[$index];
                $url = '/' . $seg . $url;
                ++ $index;
            } while (($maxCount > $index) && (false !== ($pos = strpos($path, $url))) && (0 !== $pos));
        }

        // 比对请求
        $requestUri = $this->getRequestUri();

        if ('' !== $requestUri && '/' !== substr($requestUri, 0, 1)) {
            $requestUri = '/' . $requestUri;
        }

        if ($url) {
            $prefix = $this->getUrlencodedPrefix($requestUri, $url);

            if (false !== $prefix) {
                return $this->baseUrl = $prefix;
            }

            $prefix = $this->getUrlencodedPrefix($requestUri, dirname($url));

            if (false !== $prefix) {
                return $this->baseUrl = rtrim($prefix, '/') . '/';
            }
        }

        $basename = basename($url);
        if (empty($basename) || ! strpos(rawurldecode($requestUri), $basename)) {
            return $this->baseUrl = '';
        }

        if ((strlen($requestUri) >= strlen($url)) && ((false !== ($pos = strpos($requestUri, $url))) && ($pos !== 0))) {
            $url = substr($requestUri, 0, $pos + strlen($url));
        }

        return $this->baseUrl = rtrim($url, '/') . '/';
    }

    /**
     * 请求参数
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (! is_null($this->requestUri)) {
            return $this->requestUri;
        }

        $requestUri = $this->headers->get('X_REWRITE_URL', $this->server->get('REQUEST_URI', ''));

        if (! $requestUri) {
            $requestUri = $this->server->get('ORIG_PATH_INFO');

            if ($this->server->get('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->get('QUERY_STRING');
            }
        }

        return $this->requestUri = $requestUri;
    }

    /**
     * 判断字符串是否为数字
     *
     * @param mixed $value
     * @since bool
     */
    protected function isInt($value)
    {
        if (is_int($value)) {
            return true;
        }

        return ctype_digit(strval($value));
    }

    /**
     * pathinfo 处理
     *
     * @param string $pathInfo
     * @return string
     */
    protected function parsePathInfo($pathInfo)
    {
        if ($pathInfo && $this->getOption('html_suffix')) {
            $suffix = substr($this->getOption('html_suffix'), 1);
            $pathInfo = preg_replace('/\.' . $suffix . '$/', '', $pathInfo);
        }

        $pathInfo = empty($pathInfo) ? '/' : $pathInfo;

        return $pathInfo;
    }

    /**
     * 格式化查询参数
     * 
     * @param string $queryString
     * @return string
     */
    protected function normalizeQueryString($queryString)
    {
        if ('' == $queryString) {
            return '';
        }

        $parts = [];

        foreach (explode('&', $queryString) as $item) {
            if ($item === "" || strpos($item, static::PATHINFO_URL . '=') === 0) {
                continue;
            }

            $parts[] = $item;
        }

        return implode('&', $parts);
    }

    /**
     * 取得请求输入源
     *
     * @return \Queryyetsimple\Http\Bag
     */
    protected function getInputSource()
    {
        return $this->getMethod() == static::METHOD_GET ? $this->query : $this->request;
    }

    /**
     * 是否为空字符串
     *
     * @param string $key
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $value = $this->input($key);

        return is_string($value) && trim($value) === '';
    }

    /**
     * URL 前缀编码
     * 
     * @param string $strings
     * @param string $prefix
     * @return string|boolean
     */
    protected function getUrlencodedPrefix(string $strings, string $prefix)
    {
        if (0 !== strpos(rawurldecode($strings), $prefix)) {
            return false;
        }

        $len = strlen($prefix);

        if (preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $strings, $matches)) {
            return $matches[0];
        }

        return false;
    }

    /**
     * 对象转数组
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * 实现 ArrayAccess::offsetExists
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->all());
    }

    /**
     * 实现 ArrayAccess::offsetGet
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return data_get($this->all(), $offset);
    }

    /**
     * 实现 ArrayAccess::offsetSet
     *
     * @param string $offset
     * @param mixed $value
     * @return mixed
     */
    public function offsetSet($offset, $value)
    {
        return $this->getInputSource()->set($offset, $value);
    }

    /**
     * 实现 ArrayAccess::offsetUnset
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        return $this->getInputSource()->remove($offset);
    }

    /**
     * 是否存在输入值
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return ! is_null($this->__get($key));
    }

    /**
     * 获取输入值
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
}