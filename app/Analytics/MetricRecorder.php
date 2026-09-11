<?php

namespace App\Analytics;

use App\Models\Website;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MetricRecorder
{
    public function record(Website $website, Dimensions $dimensions): void
    {
        $websiteId = $website->getKey();
        $date = now($website->timezone)->toDateString();

        $rows = collect(Metric::cases())->map(fn (Metric $metric): array => [
            'website_id' => $websiteId,
            'date' => $date,
            'metric' => $metric->value,
            'value' => $dimensions->value($metric),
            'count' => 0,
        ])->all();

        DB::transaction(function () use ($rows, $websiteId, $date): void {
            DB::table('daily_metrics')->insertOrIgnore($rows);

            DB::table('daily_metrics')
                ->where('website_id', $websiteId)
                ->where('date', $date)
                ->where(function (Builder $query) use ($rows): void {
                    foreach ($rows as $row) {
                        $query->orWhere(function (Builder $query) use ($row): void {
                            $query
                                ->where('metric', $row['metric'])
                                ->where('value', $row['value']);
                        });
                    }
                })
                ->increment('count');
        });
    }
}
