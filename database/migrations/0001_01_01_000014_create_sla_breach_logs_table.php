<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_breach_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sla_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('breach_type', ['response', 'resolution']);
            $table->integer('target_minutes');
            $table->integer('actual_minutes');
            $table->timestamp('breached_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_breach_logs');
    }
};
