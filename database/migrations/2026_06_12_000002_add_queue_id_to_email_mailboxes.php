<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_mailboxes', function (Blueprint $table) {
            // Default queue that tickets auto-created from this mailbox land in.
            $table->foreignId('queue_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('queues')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('email_mailboxes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('queue_id');
        });
    }
};
