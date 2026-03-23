<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('requester_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('service_catalog_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sla_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['incident', 'service_request', 'problem', 'change']);
            $table->enum('status', ['new', 'open', 'pending', 'on_hold', 'resolved', 'closed', 'cancelled']);
            $table->enum('priority', ['critical', 'high', 'medium', 'low']);
            $table->enum('impact', ['extensive', 'significant', 'moderate', 'minor'])->default('moderate');
            $table->enum('urgency', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('source', ['email', 'portal', 'phone', 'chat', 'api', 'monitoring'])->default('portal');
            $table->string('subject');
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->timestamp('sla_response_due_at')->nullable();
            $table->timestamp('sla_resolution_due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('sla_response_breached')->default(false);
            $table->boolean('sla_resolution_breached')->default(false);
            $table->boolean('is_escalated')->default(false);
            $table->tinyInteger('escalation_level')->default(0);
            $table->foreignId('parent_ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'assigned_to_user_id', 'status']);
            $table->index(['status', 'sla_response_due_at']);
            $table->index(['status', 'sla_resolution_due_at']);
            $table->index(['type', 'status']);
            $table->index(['requester_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
