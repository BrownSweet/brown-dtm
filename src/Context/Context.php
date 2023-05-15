<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace Dtm\Context;

use Dtm\Context\BaseContext;
class Context implements ContextInterface
{
    protected static array $nonCoContext = [];

    public static function set(string $id, $value)
    {
        if (static::isUseCoroutineExtension()) {
            return BaseContext::set($id, $value);
        }

        static::$nonCoContext[$id] = $value;
        return $value;
    }

    public static function get(string $id, $default = null, $coroutineId = null)
    {
        if (static::isUseCoroutineExtension()) {
            return BaseContext::get($id, $default, $coroutineId);
        }

        return static::$nonCoContext[$id] ?? $default;
    }

    public static function getContainer()
    {
        if (static::isUseCoroutineExtension()) {
            return BaseContext::getContainer();
        }

        return static::$nonCoContext;
    }


    private static function isUseCoroutineExtension(): bool
    {
        return extension_loaded('Swow') || extension_loaded('Swoole');
    }
}