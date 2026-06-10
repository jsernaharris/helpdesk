<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_mailboxes', function (Blueprint $table) {
            // Which transport this mailbox uses for inbound polling and outbound send.
            $table->string('driver')->default('imap')->after('email_address');

            // Microsoft Graph (app-only / client-credentials) credential vault.
            // The client secret is encrypted at rest via the model's `encrypted` cast.
            $table->string('graph_tenant_id')->nullable()->after('smtp_password');
            $table->string('graph_client_id')->nullable()->after('graph_tenant_id');
            $table->text('graph_client_secret')->nullable()->after('graph_client_id');
            // Shared mailbox UPN / address or object id (the {id} in /users/{id}).
            $table->string('graph_user_id')->nullable()->after('graph_client_secret');
        });

        // IMAP/SMTP fields are not used by Graph mailboxes, so they must be optional.
        Schema::table('email_mailboxes', function (Blueprint $table) {
            $table->string('imap_host')->nullable()->change();
            $table->integer('imap_port')->default(993)->nullable()->change();
            $table->string('imap_username')->nullable()->change();
            $table->text('imap_password')->nullable()->change();
            $table->string('smtp_host')->nullable()->change();
            $table->integer('smtp_port')->default(587)->nullable()->change();
            $table->string('smtp_username')->nullable()->change();
            $table->text('smtp_password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('email_mailboxes', function (Blueprint $table) {
            $table->dropColumn([
                'driver', 'graph_tenant_id', 'graph_client_id',
                'graph_client_secret', 'graph_user_id',
            ]);
        });
    }
};
