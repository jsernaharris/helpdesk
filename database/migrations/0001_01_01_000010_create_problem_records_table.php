<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problem_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->boolean('known_error')->default(false);
            $table->enum('status', ['open', 'investigating', 'root_cause_identified', 'resolved', 'closed']);
            $table->timestamps();
        });

        Schema::create('problem_incident', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['problem_record_id', 'ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_incident');
        Schema::dropIfExists('problem_records');
    }
};
