<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Entra/Azure AD object id (oid claim); set once on first SSO login.
            $table->string('azure_oid')->nullable()->unique()->after('email');
            // Which identity source created/owns this account: 'local' or 'azure'.
            $table->string('auth_provider')->default('local')->after('azure_oid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['azure_oid', 'auth_provider']);
        });
    }
};
