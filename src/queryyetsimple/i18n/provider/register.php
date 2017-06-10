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
 * i18n.register 服务提供者
 *
 * @author Xiangmin Liu<635750556@qq.com>
 * @package $$
 * @since 2017.05.12
 * @version 1.0
 */
return [ 
        'singleton@i18n' => [ 
                'queryyetsimple\i18n\i18n',
                function ($oProject) {
                    $arrOption = [ ];
                    foreach ( [ 
                            
                            'on',
                            'switch',
                            'cookie_app',
                            'default',
                            'auto_accept' 
                    ] as $strOption ) {
                        $arrOption [$strOption] = $oProject ['option']->get ( 'i18n\\' . $strOption );
                    }
                    
                    return new queryyetsimple\i18n\i18n ( $oProject, $arrOption );
                } 
        ] 
];
