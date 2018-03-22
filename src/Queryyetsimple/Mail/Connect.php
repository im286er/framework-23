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


namespace Queryyetsimple\Mail;

use Swift_Mailer;
use Swift_Transport;
use Swift_Mime_Message;
use Swift_Events_EventListener;
use Queryyetsimple\Option\TClass;

/**
 * connect 驱动抽象类
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.08.26
 * @version 1.0
 */
abstract class Connect implements Swift_Transport
{
    use TClass;

    /**
     * swift mailer
     *
     * @var \Swift_Mailer
     */
    protected $objSwiftMailer;

    /**
     * 构造函数
     *
     * @param array $arrOption
     * @return void
     */
    public function __construct(array $arrOption = [])
    {
        $this->options($arrOption);
        $this->swiftMailer();
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        return $this->getSwiftMailer()->send($message, $failedRecipients);
    }

    /**
     * 返回 swift mailer
     *
     * @return \Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->objSwiftMailer;
    }

    /**
     * 生成 swift mailer
     *
     * @return \Swift_Mailer
     */
    protected function swiftMailer()
    {
        return $this->objSwiftMailer = new Swift_Mailer($this->makeTransport());
    }
}