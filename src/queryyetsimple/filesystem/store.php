<?php
// [$QueryPHP] The PHP Framework For Code Poem As Free As Wind. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace queryyetsimple\filesystem;

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

use queryyetsimple\filesystem\interfaces\connect;
use queryyetsimple\filesystem\interfaces\store as interfaces_store;

/**
 * filesystem 存储
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.08.29
 * @version 1.0
 */
class store implements interfaces_store {
    
    /**
     * 连接驱动
     *
     * @var \queryyetsimple\filesystem\interfaces\connect
     */
    protected $oConnect;
    
    /**
     * 构造函数
     *
     * @param \queryyetsimple\filesystem\interfaces\connect $oConnect            
     * @return void
     */
    public function __construct(connect $oConnect) {
        $this->oConnect = $oConnect;
    }
    
    /**
     * 缺省方法
     *
     * @param 方法名 $sMethod            
     * @param 参数 $arrArgs            
     * @return mixed
     */
    public function __call($sMethod, $arrArgs) {
        return call_user_func_array ( [ 
                $this->oConnect,
                $sMethod 
        ], $arrArgs );
    }
}