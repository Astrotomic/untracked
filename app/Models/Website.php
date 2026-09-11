<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'domain', 'timezone', 'should_track_bots'])]
class Website extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'uuid';

    protected static function booted(): void
    {
        static::creating(function (Website $website): void {
            $website->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'should_track_bots' => 'boolean',
        ];
    }

    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(DailyMetric::class);
    }
}
