<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email_address');
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->enum('imap_encryption', ['ssl', 'tls', 'none'])->default('ssl');
            $table->string('imap_username');
            $table->text('imap_password');
            $table->string('smtp_host');
            $table->integer('smtp_port')->default(587);
            $table->enum('smtp_encryption', ['ssl', 'tls', 'none'])->default('tls');
            $table->string('smtp_username');
            $table->text('smtp_password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->boolean('auto_create_tickets')->default(true);
            $table->enum('default_priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('default_type', ['incident', 'service_request'])->default('incident');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_mailboxes');
    }
};
