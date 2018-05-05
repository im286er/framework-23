<?php declare(strict_types=1);
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
namespace Leevel\Auth;

use Leevel\{
    Mvc\IModel,
    Session\ISession,
    Validate\IValidate,
    Encryption\IEncryption
};

/**
 * auth.session
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.09.07
 * @version 1.0
 */
class Session extends Connect implements IConnect
{

    /**
     * session
     *
     * @var \Leevel\Session\ISession
     */
    protected $oSession;

    /**
     * 构造函数
     *
     * @param \Leevel\Mvc\IModel $oUser
     * @param \Leevel\Encryption\IEncryption $oEncryption
     * @param \Leevel\Validate\IValidate $oValidate
     * @param \Leevel\Session\ISession $oSession
     * @param array $arrOption
     * @return void
     */
    public function __construct(IModel $oUser, IEncryption $oEncryption, IValidate $oValidate, ISession $oSession, array $arrOption = [])
    {
        $this->oSession = $oSession;

        parent::__construct($oUser, $oEncryption, $oValidate, $arrOption);
    }

    /**
     * 设置认证名字
     *
     * @param \Leevel\Mvc\IModel $oUser
     * @return void
     */
    protected function setLoginTokenName($oUser)
    {
    }

    /**
     * 数据持久化
     *
     * @param string $strKey
     * @param string $mixValue
     * @param mixed $mixExpire
     * @return void
     */
    protected function setPersistence($strKey, $mixValue, $mixExpire = null)
    {
        $this->oSession->set($strKey, $mixValue, [
            'expire' => $mixExpire
        ]);
    }

    /**
     * 获取持久化数据
     *
     * @param string $strKey
     * @return mixed
     */
    protected function getPersistence($strKey)
    {
        return $this->oSession->get($strKey);
    }

    /**
     * 删除持久化数据
     *
     * @param string $strKey
     * @return void
     */
    protected function deletePersistence($strKey)
    {
        $this->oSession->delele($strKey);
    }
}
