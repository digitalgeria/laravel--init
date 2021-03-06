<?php

namespace Domains\CreateProjectForm\Sections\Metadata;

use Domains\Support\Enum\EmulatesEnum;

class PhpVersion
{
    use EmulatesEnum;

    public const v7_4 = '7.4';
    public const v8_0 = '8.0';

    public static function latest(): string
    {
        return self::v8_0;
    }
}
