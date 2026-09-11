<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'domain', 'timezone', 'track_bots'])]
class Website extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Website $website): void {
            $website->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'track_bots' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(DailyMetric::class);
    }
}
