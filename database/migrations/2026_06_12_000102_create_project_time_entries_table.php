<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            // Denormalized from the project for clean tenant scoping and exports.
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            // The technician the time is attributed to.
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Optional link so ticket work can roll into a project's totals.
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->date('work_date');
            // Stored in minutes for precision; entered and displayed as hours.
            $table->unsignedInteger('minutes');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'work_date']);
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_time_entries');
    }
};
