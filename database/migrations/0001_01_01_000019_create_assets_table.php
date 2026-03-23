<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('asset_tag')->unique();
            $table->string('type');
            $table->string('serial_number')->nullable();
            $table->enum('status', ['active', 'retired', 'in_repair'])->default('active');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asset_id', 'ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_ticket');
        Schema::dropIfExists('assets');
    }
};
