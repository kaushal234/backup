<?php

declare(strict_types=1);

namespace App\Pi\Utils;

class ArrayUtils
{
    public static function toMultiDimensional($array, $separator = '.')
    {
        if (false === mb_strpos(implode('', array_keys($array)), $separator)) {
            return $array;
        }
        $matchingKeys = [];
        foreach ($array as $key => $value) {
            if (false !== mb_strpos($key, $separator)) {
                [$fragment, $subKey] = explode($separator, $key, 2);
                $matchingKeys[] = $fragment;
                if (!\array_key_exists($fragment, $array)) {
                    $array[$fragment] = [];
                }
                $array[$fragment][$subKey] = $value;
                unset($array[$key]);
            }
        }
        foreach (array_unique($matchingKeys) as $key) {
            $array[$key] = self::toMultiDimensional($array[$key]);
        }

        return $array;
    }
}
