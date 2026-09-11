<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_metrics', function (Blueprint $table): void {
            $table->foreignUuid('website_id')->index()->constrained('websites', 'uuid')->cascadeOnDelete();
            $table->date('date')->index();
            $table->string('metric', 32)->index();
            $table->string('value', 500)->index();
            $table->unsignedBigInteger('count')->default(0);

            $table->primary(['website_id', 'date', 'metric', 'value']);

            // PostgreSQL can combine these indexes for arbitrary dashboard filters.
            // Other database engines may benefit from workload-specific composite indexes.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
