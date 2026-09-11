<?php

namespace App\Concerns;

use Mockery;
use Mockery\MockInterface;

trait Resolvable
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function fake(): MockInterface
    {
        return app()->instance(static::class, Mockery::mock(static::class));
    }
}
