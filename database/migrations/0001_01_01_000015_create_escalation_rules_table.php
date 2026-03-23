<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('trigger_type', ['sla_warning', 'sla_breach', 'no_response', 'priority_change']);
            $table->integer('trigger_minutes_before')->nullable();
            $table->tinyInteger('escalation_level');
            $table->enum('action_type', ['assign_team', 'assign_user', 'notify_email', 'change_priority']);
            $table->json('action_target');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalation_rules');
    }
};
