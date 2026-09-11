<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use Normalizer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::unguard();

        Stringable::macro('normalize', function (int $form = Normalizer::FORM_C): Stringable {
            $normalized = Normalizer::normalize((string) $this, $form);

            if ($normalized === false) {
                throw new InvalidArgumentException('Unable to normalize invalid UTF-8 string.');
            }

            return new Stringable($normalized);
        });

        Str::macro('equals', function (string $string1, string $string2): bool {
            if ($string1 === $string2) {
                return true;
            }

            $string1 = (string) Str::of($string1)->normalize()->convertCase();
            $string2 = (string) Str::of($string2)->normalize()->convertCase();

            return $string1 === $string2;
        });
    }
}
