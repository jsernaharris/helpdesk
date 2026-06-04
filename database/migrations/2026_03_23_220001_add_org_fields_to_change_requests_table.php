<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('change_requests', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('ticket_id')->constrained()->nullOnDelete();
            $table->foreignId('change_category_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->after('change_category_id')->constrained('users')->nullOnDelete();
            $table->text('business_justification')->nullable()->after('test_plan');
            $table->text('impact_assessment')->nullable()->after('business_justification');
            $table->text('communication_plan')->nullable()->after('impact_assessment');
            $table->integer('approval_level_required')->default(1)->after('cab_notes');
            $table->integer('current_approval_level')->default(0)->after('approval_level_required');
            $table->timestamp('submitted_at')->nullable()->after('current_approval_level');
            $table->timestamp('review_completed_at')->nullable()->after('submitted_at');
            $table->text('post_implementation_notes')->nullable()->after('review_completed_at');

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'scheduled_start_at']);
        });

        // Backfill organization_id from the ticket relationship.
        // Written as a correlated subquery so it runs on both MySQL and SQLite.
        \DB::statement('UPDATE change_requests SET organization_id = (SELECT t.organization_id FROM tickets t WHERE t.id = change_requests.ticket_id) WHERE organization_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('change_requests', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['change_category_id']);
            $table->dropForeign(['requested_by_user_id']);
            $table->dropIndex(['organization_id', 'status']);
            $table->dropIndex(['organization_id', 'scheduled_start_at']);
            $table->dropColumn([
                'organization_id', 'change_category_id', 'requested_by_user_id',
                'business_justification', 'impact_assessment', 'communication_plan',
                'approval_level_required', 'current_approval_level',
                'submitted_at', 'review_completed_at', 'post_implementation_notes',
            ]);
        });
    }
};
