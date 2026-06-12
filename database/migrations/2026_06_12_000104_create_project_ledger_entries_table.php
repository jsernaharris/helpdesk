<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            // The actor/author; null for system-generated rows with no user context.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // 'note' (manual work log) or an event type: created, status_changed,
            // member_added, member_removed, time_logged.
            $table->string('type');
            $table->text('description');
            // Internal rows (staffing, hours) are hidden from the customer portal.
            $table->boolean('is_internal')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'created_at']);
            $table->index(['organization_id', 'is_internal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_ledger_entries');
    }
};
