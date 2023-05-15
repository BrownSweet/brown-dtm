<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-13 08:49
 */

namespace Dtm\Context;

interface ContextInterface
{
    public static function set(string $id, $value);

    public static function get(string $id, $default = null, $coroutineId = null);

    public static function getContainer();
}