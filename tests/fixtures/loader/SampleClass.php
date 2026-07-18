<?php
declare(strict_types=1);

namespace Tests\Fixtures\Loader;

final class SampleClass
{
    public static function value(): string
    {
        return 'loaded';
    }
}
