<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // The mailbox an email-sourced ticket arrived on, so outbound replies
            // are sent back out from the same shared mailbox. Null for web tickets.
            $table->foreignId('email_mailbox_id')
                ->nullable()
                ->after('source')
                ->constrained('email_mailboxes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('email_mailbox_id');
        });
    }
};
