<?php

use Abather\CustomDataHelpers\DataHelper;

if (! function_exists('_data_has')) {
    /**
     * Determine if a key / property exists on an array or object using custom separator.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     */
    function _data_has($target, $key, string $separator = '.'): bool
    {
        return DataHelper::has($target, $key, $separator);
    }
}

if (! function_exists('_data_get')) {
    /**
     * Get an item from an array or object using custom separator.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function _data_get($target, $key, string $separator = '.', $default = null)
    {
        return DataHelper::get($target, $key, $separator, $default);
    }
}

if (! function_exists('_data_set')) {
    /**
     * Set an item on an array or object using custom separator.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @return mixed
     */
    function _data_set(&$target, $key, $value, bool $overwrite = true, string $separator = '.')
    {
        return DataHelper::set($target, $key, $value, $overwrite, $separator);
    }
}

if (! function_exists('_data_forget')) {
    /**
     * Remove / unset an item from an array or object using custom separator.
     *
     * @param  mixed  $target
     * @param  string|array|int|null  $key
     * @return mixed
     */
    function _data_forget(&$target, $key, string $separator = '.')
    {
        return DataHelper::forget($target, $key, $separator);
    }
}
