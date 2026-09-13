<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table): void {
            $table->json('metric_preferences')->nullable();
        });

        DB::table('websites')->update([
            'metric_preferences' => json_encode([
                'path' => true,
                'country' => true,
                'client' => true,
                'os' => true,
                'device' => true,
                'format' => true,
                'referrer' => true,
                'utm_source' => true,
                'utm_medium' => true,
                'utm_campaign' => true,
                'utm_term' => true,
                'utm_content' => true,
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table): void {
            $table->dropColumn('metric_preferences');
        });
    }
};
