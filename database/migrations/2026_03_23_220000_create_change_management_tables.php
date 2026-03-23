<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-org change management policies
        Schema::create('change_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->boolean('require_cab_for_normal')->default(true);
            $table->boolean('require_cab_for_standard')->default(false);
            $table->boolean('require_cab_for_emergency')->default(false);
            $table->integer('min_lead_time_hours')->default(48);
            $table->integer('emergency_lead_time_hours')->default(0);
            $table->boolean('require_rollback_plan')->default(true);
            $table->boolean('require_test_plan')->default(true);
            $table->boolean('require_implementation_plan')->default(true);
            $table->boolean('allow_customer_submit')->default(true);
            $table->integer('auto_approve_standard')->default(false);
            $table->text('change_window_notes')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique('organization_id');
        });

        // Per-org change categories / templates
        Schema::create('change_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('default_type', ['standard', 'normal', 'emergency'])->default('normal');
            $table->enum('default_risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('template_implementation_plan')->nullable();
            $table->text('template_rollback_plan')->nullable();
            $table->text('template_test_plan')->nullable();
            $table->boolean('cab_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        // Per-org CAB (Change Advisory Board) members
        Schema::create('cab_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['chair', 'member', 'advisor'])->default('member');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        // Multi-level approval tracking
        Schema::create('change_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('decision', ['approved', 'rejected', 'deferred']);
            $table->text('comments')->nullable();
            $table->integer('approval_level')->default(1);
            $table->timestamps();

            $table->index(['change_request_id', 'approval_level']);
        });

        // Post-implementation reviews
        Schema::create('change_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('objectives_met')->default(true);
            $table->boolean('on_schedule')->default(true);
            $table->boolean('within_budget')->default(true);
            $table->boolean('incidents_caused')->default(false);
            $table->text('incidents_description')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->text('improvement_actions')->nullable();
            $table->enum('overall_rating', ['successful', 'partially_successful', 'failed'])->default('successful');
            $table->timestamps();

            $table->unique('change_request_id');
        });

        // Change blackout periods per org
        Schema::create('change_blackout_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('reason')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('allow_emergency')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_blackout_periods');
        Schema::dropIfExists('change_reviews');
        Schema::dropIfExists('change_approvals');
        Schema::dropIfExists('cab_members');
        Schema::dropIfExists('change_categories');
        Schema::dropIfExists('change_policies');
    }
};
