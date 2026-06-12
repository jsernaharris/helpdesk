<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            // A sender domain (e.g. harriscomputer.com). Unique so inbound mail
            // routes to exactly one organization.
            $table->string('domain')->unique();
            $table->timestamps();
        });

        // Backfill from the legacy single-domain column so existing routing keeps
        // working. The organizations.email_domain column is left in place but is no
        // longer consulted for routing.
        DB::table('organizations')
            ->whereNotNull('email_domain')
            ->where('email_domain', '!=', '')
            ->orderBy('id')
            ->get(['id', 'email_domain'])
            ->each(function ($org) {
                $domain = strtolower(trim($org->email_domain));
                if ($domain === '' || DB::table('organization_domains')->where('domain', $domain)->exists()) {
                    return;
                }
                DB::table('organization_domains')->insert([
                    'organization_id' => $org->id,
                    'domain' => $domain,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_domains');
    }
};
