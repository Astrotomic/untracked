<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_metrics', function (Blueprint $table): void {
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('metric', 32);
            $table->string('value', 500);
            $table->unsignedBigInteger('count')->default(0);

            $table->primary(['website_id', 'date', 'metric', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
