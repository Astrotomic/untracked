<?php

namespace Tests\Assertions;

use App\Models\DailyMetric;
use App\Models\Website;
use Astrotomic\PhpunitAssertions\ArrayAssertions;
use Illuminate\Support\Collection;

final class DailyMetricsAssertions
{
    public static function assertEquals(array $expected, Website $website): void
    {
        $actual = DailyMetric::query()
            ->where('website_uuid', $website->getKey())
            ->get()
            ->groupBy('metric')
            ->map(static fn (Collection $metrics): array => $metrics
                ->mapWithKeys(static fn (DailyMetric $metric): array => [
                    $metric->value => $metric->count,
                ])
                ->all())
            ->all();

        ArrayAssertions::assertEquals($expected, $actual);
    }
}
