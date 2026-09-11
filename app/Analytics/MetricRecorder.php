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

        $rows = collect(Metric::cases())
            ->map(function (Metric $metric) use ($websiteId, $date, $dimensions): ?array {
                $value = $dimensions->value($metric);

                if ($value === null || $value === '') {
                    return null;
                }

                return [
                    'website_id' => $websiteId,
                    'date' => $date,
                    'metric' => $metric->value,
                    'value' => $value,
                    'count' => 0,
                ];
            })
            ->filter()
            ->values()
            ->all();

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
