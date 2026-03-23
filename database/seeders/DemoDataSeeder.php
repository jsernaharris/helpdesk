<?php

namespace Database\Seeders;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Organization;
use App\Models\ServiceCatalog;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketThread;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $msp = Organization::where('is_msp', true)->first();
        $admin = User::where('email', 'admin@msphelpdesk.com')->first();
        $tech = User::where('email', 'tech@msphelpdesk.com')->first();

        // Create customer organizations
        $acme = Organization::firstOrCreate(['slug' => 'acme-corp'], [
            'name' => 'Acme Corporation',
            'email_domain' => 'acme.com',
            'phone' => '555-100-1000',
            'address' => '100 Main St',
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75201',
            'is_active' => true,
        ]);

        $globex = Organization::firstOrCreate(['slug' => 'globex-inc'], [
            'name' => 'Globex Inc',
            'email_domain' => 'globex.com',
            'phone' => '555-200-2000',
            'address' => '200 Oak Ave',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '73301',
            'is_active' => true,
        ]);

        $wayne = Organization::firstOrCreate(['slug' => 'wayne-enterprises'], [
            'name' => 'Wayne Enterprises',
            'email_domain' => 'wayne.com',
            'phone' => '555-300-3000',
            'is_active' => true,
        ]);

        // Create customer users
        $acmeAdmin = User::firstOrCreate(['email' => 'admin@acme.com'], [
            'name' => 'John Smith',
            'organization_id' => $acme->id,
            'password' => Hash::make('password'),
        ]);
        $acmeAdmin->assignRole('customer_admin');

        $acmeUser = User::firstOrCreate(['email' => 'jane@acme.com'], [
            'name' => 'Jane Doe',
            'organization_id' => $acme->id,
            'password' => Hash::make('password'),
        ]);
        $acmeUser->assignRole('customer_user');

        $globexUser = User::firstOrCreate(['email' => 'bob@globex.com'], [
            'name' => 'Bob Wilson',
            'organization_id' => $globex->id,
            'password' => Hash::make('password'),
        ]);
        $globexUser->assignRole('customer_user');

        $wayneAdmin = User::firstOrCreate(['email' => 'bruce@wayne.com'], [
            'name' => 'Bruce Wayne',
            'organization_id' => $wayne->id,
            'password' => Hash::make('password'),
        ]);
        $wayneAdmin->assignRole('customer_admin');

        // Create teams
        $l1 = Team::firstOrCreate(['name' => 'Level 1 Support'], ['description' => 'First line support', 'is_active' => true]);
        $l2 = Team::firstOrCreate(['name' => 'Level 2 Support'], ['description' => 'Advanced support', 'is_active' => true]);
        $net = Team::firstOrCreate(['name' => 'Network Team'], ['description' => 'Network infrastructure', 'is_active' => true]);

        $l1->users()->syncWithoutDetaching([$tech->id => ['is_lead' => false]]);
        $l2->users()->syncWithoutDetaching([$admin->id => ['is_lead' => true]]);

        // Create service catalogs
        foreach (['Desktop Support', 'Network Management', 'Email & Collaboration', 'Server Administration', 'Security Services'] as $service) {
            foreach ([$acme, $globex, $wayne] as $org) {
                ServiceCatalog::firstOrCreate([
                    'organization_id' => $org->id,
                    'name' => $service,
                ], [
                    'description' => "{$service} for {$org->name}",
                    'is_active' => true,
                ]);
            }
        }

        // Create sample tickets
        $tickets = [
            ['org' => $acme, 'user' => $acmeUser, 'subject' => 'Cannot access email', 'desc' => 'Outlook keeps showing "Disconnected" status. Tried restarting but same issue.', 'type' => 'incident', 'priority' => 'high', 'status' => 'open', 'assigned' => $tech],
            ['org' => $acme, 'user' => $acmeAdmin, 'subject' => 'Request new laptop for new hire', 'desc' => 'We have a new employee starting next Monday. Need a standard laptop setup with Office 365 and VPN access.', 'type' => 'service_request', 'priority' => 'medium', 'status' => 'new', 'assigned' => null],
            ['org' => $globex, 'user' => $globexUser, 'subject' => 'Printer not working on 3rd floor', 'desc' => 'The HP printer near the conference room shows "offline" on all computers. Paper and toner are fine.', 'type' => 'incident', 'priority' => 'low', 'status' => 'pending', 'assigned' => $tech],
            ['org' => $globex, 'user' => $globexUser, 'subject' => 'VPN disconnects frequently', 'desc' => 'Remote VPN connection drops every 15-20 minutes. Have to reconnect each time. This started after the last update.', 'type' => 'incident', 'priority' => 'high', 'status' => 'open', 'assigned' => $admin],
            ['org' => $wayne, 'user' => $wayneAdmin, 'subject' => 'Need additional Office 365 licenses', 'desc' => 'We need 10 additional Office 365 Business Premium licenses for our new department.', 'type' => 'service_request', 'priority' => 'medium', 'status' => 'open', 'assigned' => $tech],
            ['org' => $acme, 'user' => $acmeUser, 'subject' => 'Blue screen on workstation', 'desc' => 'Getting BSOD with error CRITICAL_PROCESS_DIED about once a day. Started happening last week.', 'type' => 'incident', 'priority' => 'critical', 'status' => 'open', 'assigned' => $admin],
            ['org' => $wayne, 'user' => $wayneAdmin, 'subject' => 'Slow internet in building B', 'desc' => 'All users in building B are experiencing very slow internet speeds since this morning.', 'type' => 'incident', 'priority' => 'high', 'status' => 'new', 'assigned' => null],
        ];

        foreach ($tickets as $t) {
            $ticket = Ticket::firstOrCreate(
                ['subject' => $t['subject'], 'organization_id' => $t['org']->id],
                [
                    'organization_id' => $t['org']->id,
                    'requester_user_id' => $t['user']->id,
                    'assigned_to_user_id' => $t['assigned']?->id,
                    'type' => $t['type'],
                    'status' => $t['status'],
                    'priority' => $t['priority'],
                    'source' => 'portal',
                    'description' => $t['desc'],
                    'impact' => 'moderate',
                    'urgency' => $t['priority'],
                ]
            );

            if ($ticket->wasRecentlyCreated) {
                TicketThread::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $t['user']->id,
                    'type' => 'reply',
                    'body' => $t['desc'],
                ]);

                TicketActivity::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $t['user']->id,
                    'action' => 'created',
                    'new_value' => $ticket->ticket_number,
                ]);

                // Add a tech reply on some tickets
                if ($t['status'] === 'open' && $t['assigned']) {
                    TicketThread::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $t['assigned']->id,
                        'type' => 'reply',
                        'body' => 'I\'m looking into this issue now. Will update shortly.',
                    ]);
                    $ticket->update(['first_responded_at' => now()]);
                }
            }
        }

        // Knowledge base
        $generalCat = KbCategory::firstOrCreate(['slug' => 'general'], [
            'name' => 'General',
            'description' => 'General IT support articles',
            'is_active' => true,
        ]);

        $networkCat = KbCategory::firstOrCreate(['slug' => 'networking'], [
            'name' => 'Networking',
            'description' => 'Network troubleshooting and guides',
            'is_active' => true,
        ]);

        $emailCat = KbCategory::firstOrCreate(['slug' => 'email'], [
            'name' => 'Email & Office 365',
            'description' => 'Email configuration and Office 365 guides',
            'is_active' => true,
        ]);

        KbArticle::firstOrCreate(['slug' => 'vpn-setup-guide'], [
            'title' => 'VPN Setup Guide',
            'category_id' => $networkCat->id,
            'author_id' => $admin->id,
            'content' => "<h2>Setting up VPN Access</h2>\n<p>Follow these steps to configure your VPN connection:</p>\n<ol>\n<li>Download the VPN client from the IT portal</li>\n<li>Install with default settings</li>\n<li>Enter the server address provided by your IT admin</li>\n<li>Use your network credentials to connect</li>\n</ol>\n<p>If you experience disconnection issues, try switching between TCP and UDP protocols in the VPN settings.</p>",
            'excerpt' => 'Step-by-step guide to set up and troubleshoot VPN connections.',
            'visibility' => 'public',
            'status' => 'published',
            'published_at' => now(),
            'is_pinned' => true,
        ]);

        KbArticle::firstOrCreate(['slug' => 'outlook-troubleshooting'], [
            'title' => 'Outlook Troubleshooting',
            'category_id' => $emailCat->id,
            'author_id' => $admin->id,
            'content' => "<h2>Common Outlook Issues</h2>\n<h3>Outlook Shows Disconnected</h3>\n<p>Try these steps:</p>\n<ol>\n<li>Check your internet connection</li>\n<li>Restart Outlook</li>\n<li>Check if Office 365 services are running</li>\n<li>Clear Outlook cache</li>\n<li>Recreate your email profile</li>\n</ol>",
            'excerpt' => 'Solutions for common Outlook and email problems.',
            'visibility' => 'public',
            'status' => 'published',
            'published_at' => now(),
        ]);

        KbArticle::firstOrCreate(['slug' => 'password-reset'], [
            'title' => 'How to Reset Your Password',
            'category_id' => $generalCat->id,
            'author_id' => $admin->id,
            'content' => "<h2>Password Reset Process</h2>\n<p>To reset your network password:</p>\n<ol>\n<li>Go to the self-service portal at https://password.company.com</li>\n<li>Enter your username</li>\n<li>Answer your security questions</li>\n<li>Create a new password meeting the following requirements: 12+ characters, uppercase, lowercase, number, special character</li>\n</ol>",
            'excerpt' => 'Instructions for resetting your network password.',
            'visibility' => 'public',
            'status' => 'published',
            'published_at' => now(),
            'is_pinned' => true,
        ]);
    }
}
