<?php

declare(strict_types=1);

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

namespace Tests\Database\Ddd\Entity\Relation;

use Leevel\Database\Ddd\Entity;

/**
 * user.
 *
 * @author Xiangmin Liu <635750556@qq.com>
 *
 * @since 2018.10.13
 *
 * @version 1.0
 */
class User extends Entity
{
    const TABLE = 'user';

    const ID = 'id';

    const AUTO = 'id';

    const STRUCT = [
        'id'        => [],
        'name'      => [],
        'create_at' => [],
        'role'      => [
            self::MANY_MANY     => Role::class,
            'middle_entity'     => UserRole::class,
            'source_key'        => 'id',
            'target_key'        => 'id',
            'middle_source_key' => 'user_id',
            'middle_target_key' => 'role_id',
        ],
    ];

    private $id;

    private $name;

    private $createAt;

    private $role;

    public function setter(string $prop, $value)
    {
        $this->{$this->prop($prop)} = $value;

        return $this;
    }

    public function getter(string $prop)
    {
        return $this->{$this->prop($prop)};
    }
}
