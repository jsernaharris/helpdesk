<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // The service-line queue this ticket belongs to. Null for tickets not
            // routed to a queue.
            $table->foreignId('queue_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('queues')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('queue_id');
        });
    }
};
