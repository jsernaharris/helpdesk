<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['reply', 'note', 'system', 'email_inbound', 'email_outbound']);
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->string('email_message_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ticket_id', 'created_at']);
            $table->index('email_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_threads');
    }
};
