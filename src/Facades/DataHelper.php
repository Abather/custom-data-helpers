<?php

namespace Abather\CustomDataHelpers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool has(mixed $target, string|array|int|null $key, string $separator = '.')
 * @method static mixed get(mixed $target, string|array|int|null $key, mixed $default = null, string $separator = '.')
 * @method static mixed set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true, string $separator = '.')
 * @method static mixed forget(mixed &$target, string|array|int|null $key, string $separator = '.')
 *
 * @see \Abather\CustomDataHelpers\DataHelper
 */
class DataHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Abather\CustomDataHelpers\DataHelper::class;
    }
}
