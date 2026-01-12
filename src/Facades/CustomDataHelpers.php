<?php

namespace Abather\CustomDataHelpers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Abather\CustomDataHelpers\CustomDataHelpers
 */
class CustomDataHelpers extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Abather\CustomDataHelpers\CustomDataHelpers::class;
    }
}
