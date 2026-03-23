<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('timezone')->default('America/Chicago');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('business_hour_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_hours_id')->constrained('business_hours')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 0=Sun..6=Sat
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hour_periods');
        Schema::dropIfExists('business_hours');
    }
};
