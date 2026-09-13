<?php

namespace App\Models;

use App\Enums\Metric;
use App\Values\Dimensions;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property array<string, bool> $metric_preferences
 */
class Website extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'uuid';

    protected function casts(): array
    {
        return [
            'should_track_bots' => 'boolean',
            'metric_preferences' => 'array',
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function defaultMetricPreferences(): array
    {
        $preferences = [];

        foreach (Metric::cases() as $metric) {
            if ($metric->isConfigurable()) {
                $preferences[$metric->value] = true;
            }
        }

        return $preferences;
    }

    /**
     * @return HasMany<DailyMetric, $this>
     */
    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(DailyMetric::class);
    }

    public function isTracking(Metric $metric): bool
    {
        return ! $metric->isConfigurable()
            || ($this->metric_preferences[$metric->value] ?? true);
    }

    public function recordRaw(string $url, string $ip, string $userAgent, ?string $referrer): Dimensions
    {
        return $this->record(Dimensions::fromRaw(
            url: $url,
            ip: $ip,
            userAgent: $userAgent,
            referrer: $referrer,
            website: $this,
        ));
    }

    public function record(Dimensions $dimensions): Dimensions
    {
        if ($dimensions->userAgent->isBot() && ! $this->should_track_bots) {
            return $dimensions;
        }

        $date = CarbonImmutable::now($this->timezone)->toDateString();

        $rows = collect(Metric::cases())
            ->filter(fn (Metric $metric): bool => $this->isTracking($metric))
            ->map(function (Metric $metric) use ($date, $dimensions): ?array {
                $value = $dimensions->value($metric);

                if (empty($value)) {
                    return null;
                }

                return [
                    'website_uuid' => $this->getKey(),
                    'date' => $date,
                    'metric' => $metric->value,
                    'value' => $value,
                    'count' => 0,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($rows === []) {
            return $dimensions;
        }

        DB::transaction(function () use ($rows, $date): void {
            $this->dailyMetrics()->insertOrIgnore($rows);

            $this->dailyMetrics()
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

        return $dimensions;
    }
}
