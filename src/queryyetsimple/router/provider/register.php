<?php
// [$QueryPHP] The PHP Framework For Code Poem As Free As Wind. <Query Yet Simple>
// ©2010-2017 http://queryphp.com All rights reserved.
namespace queryyetsimple\router\provider;

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

use queryyetsimple\router\router;
use queryyetsimple\support\provider;

/**
 * router 服务提供者
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.05.12
 * @version 1.0
 */
class register extends provider {
    
    /**
     * 注册服务
     *
     * @return void
     */
    public function register() {
        $this->singleton ( 'router', function ($oProject) {
            $arrOption = $oProject ['option']->get ( 'url\\' );
            foreach ( [ 
                    '~apps~',
                    'default_app',
                    'default_controller',
                    'default_action' 
            ] as $strOption ) {
                $arrOption [$strOption] = $oProject ['option']->get ( $strOption );
            }
            return new router ( $oProject, $oProject ['pipeline'], $oProject ['request'], $arrOption );
        } );
    }
    
    /**
     * 可用服务提供者
     *
     * @return array
     */
    public static function providers() {
        return [ 
                'router' => 'queryyetsimple\router\router' 
        ];
    }
}
