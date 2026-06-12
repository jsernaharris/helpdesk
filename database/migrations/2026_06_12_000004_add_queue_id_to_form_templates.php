<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            // Tickets submitted through this form auto-route to this queue.
            $table->foreignId('queue_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('queues')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('queue_id');
        });
    }
};
