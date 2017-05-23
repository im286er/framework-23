<?php
// [$QueryPHP] A PHP Framework Since 2010.10.03. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
<<<queryphp
##########################################################
#   ____                          ______  _   _ ______   #
#  /     \       ___  _ __  _   _ | ___ \| | | || ___ \  #
# |   (  ||(_)| / _ \| '__|| | | || |_/ /| |_| || |_/ /  #
#  \____/ |___||  __/| |   | |_| ||  __/ |  _  ||  __/   #
#       \__   | \___ |_|    \__  || |    | | | || |      #
#     Query Yet Simple      __/  |\_|    |_| |_|\_|      #
#                          |___ /  Since 2010.10.03      #
##########################################################
queryphp;

/**
 * 调试默认配置文件
 *
 * @author Xiangmin Liu<635750556@qq.com>
 * @package $$
 * @since 2016.11.19
 * @version 1.0
 */
return [ 
        
        /**
         * ---------------------------------------------------------------
         * 显示页面调式信息
         * ---------------------------------------------------------------
         *
         * 是否开启页面调试信息，可以用来帮助排除问题
         */
        'page_trace' => false,
        
        /**
         * ---------------------------------------------------------------
         * 默认异常捕获
         * ---------------------------------------------------------------
         *
         * 接管 PHP 默认的异常回调
         */
        'exception_handle' => [ 
                'queryyetsimple\bootstrap\exception\handle',
                'exceptionHandle' 
        ],
        
        /**
         * ---------------------------------------------------------------
         * 重定向错误页面
         * ---------------------------------------------------------------
         *
         * 系统遇到错误实现的重定向
         */
        'exception_redirect' => '',
        
        /**
         * ---------------------------------------------------------------
         * 自定义错误模板
         * ---------------------------------------------------------------
         *
         * 你可以让错误消息更加适应你的应用
         */
        'exception_template' => '',
        
        /**
         * ---------------------------------------------------------------
         * 默认异常错误消息
         * ---------------------------------------------------------------
         *
         * 使用默认消息避免暴露重要的错误消息给用户
         */
        'exception_default_message' => 'error',
        
        /**
         * ---------------------------------------------------------------
         * 是否显示具体错误
         * ---------------------------------------------------------------
         *
         * 不显示具体错误消息则会采用 exception_default_message 来填充消息
         */
        'exception_show_message' => true 
];
