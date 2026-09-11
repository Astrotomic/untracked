<?php

namespace App\Analytics;

use App\Models\Website;
use Illuminate\Support\Facades\DB;

class MetricRecorder
{
    /**
     * @param array{path: string, country: string, browser: string, os: string, device: string, format: string} $dimensions
     */
    public function record(Website $website, array $dimensions): void
    {
        $date = now($website->timezone)->toDateString();

        $rows = collect(Metric::cases())->map(fn (Metric $metric): array => [
            'website_id' => $website->getKey(),
            'date' => $date,
            'metric' => $metric->value,
            'value' => $dimensions[$metric->value],
            'count' => 0,
        ])->all();

        DB::transaction(function () use ($rows): void {
            DB::table('daily_metrics')->insertOrIgnore($rows);

            foreach ($rows as $row) {
                DB::table('daily_metrics')
                    ->where('website_id', $row['website_id'])
                    ->where('date', $row['date'])
                    ->where('metric', $row['metric'])
                    ->where('value', $row['value'])
                    ->increment('count');
            }
        });
    }
}
