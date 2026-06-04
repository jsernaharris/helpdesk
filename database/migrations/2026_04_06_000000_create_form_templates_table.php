<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('fields');
            $table->boolean('is_active')->default(true);
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'organization_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('form_template_id')->nullable()->after('custom_fields')->constrained('form_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('form_template_id');
        });
        Schema::dropIfExists('form_templates');
    }
};
