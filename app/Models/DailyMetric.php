<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMetric extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'count' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Website, $this>
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
