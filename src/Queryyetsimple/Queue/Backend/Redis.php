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
namespace Leevel\Queue\Backend;

use PHPQueue\Backend\Predis;
use PHPQueue\Exception\BackendException;

/**
 * redis 存储
 *
 * @author Xiangmin Liu <635750556@qq.com>
 * @package $$
 * @since 2017.06.08
 * @version 1.0
 */
class Redis extends Predis
{

    /**
     * {@inheritdoc}
     */
    public function release($jobId = null)
    {
        $this->beforeRelease($jobId);

        if (! $this->hasQueue()) {
            throw new BackendException("No queue specified.");
        }
        $strJobData = $this->open_items[$jobId];

        // 加入执行次数
        $strJobData = json_decode($strJobData, true);
        if ($strJobData) {
            if (empty($strJobData['data']['attempts'])) {
                $strJobData['data']['attempts'] = 1;
            } else {
                $strJobData['data']['attempts'] ++;
            }
            $strJobData = json_encode($strJobData);
        }

        $booStatus = $this->getConnection()->rpush($this->queue_name, $strJobData);
        if (! $booStatus) {
            throw new BackendException("Unable to save data.");
        }
        $this->last_job_id = $jobId;
        
        $this->afterClearRelease();
    }
}