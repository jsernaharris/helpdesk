<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sla_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('priority', ['critical', 'high', 'medium', 'low']);
            $table->integer('response_time_minutes');
            $table->integer('resolution_time_minutes');
            $table->boolean('business_hours_only')->default(true);
            $table->timestamps();

            $table->unique(['sla_plan_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_targets');
        Schema::dropIfExists('sla_plans');
    }
};
