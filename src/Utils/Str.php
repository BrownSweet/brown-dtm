<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace Dtm\Utils;

class Str
{
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return $value;
    }

    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    public static function camel($value)
    {
        return lcfirst(static::studly($value));
    }

    public static function studly(string $value, string $gap = ''): string
    {
        $key = $value;

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', $gap, $value);
    }
}