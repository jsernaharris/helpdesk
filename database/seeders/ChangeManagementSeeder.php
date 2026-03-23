<?php

namespace Database\Seeders;

use App\Models\CabMember;
use App\Models\ChangeBlackoutPeriod;
use App\Models\ChangeCategory;
use App\Models\ChangePolicy;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChangeManagementSeeder extends Seeder
{
    public function run(): void
    {
        $msp = Organization::where('is_msp', true)->first();
        $admin = User::where('email', 'admin@msphelpdesk.com')->first();
        $tech = User::where('email', 'tech@msphelpdesk.com')->first();

        $orgs = Organization::where('is_msp', false)->where('is_active', true)->get();

        foreach ($orgs as $org) {
            // Create change policy per org
            ChangePolicy::firstOrCreate(
                ['organization_id' => $org->id],
                [
                    'require_cab_for_normal' => true,
                    'require_cab_for_standard' => false,
                    'require_cab_for_emergency' => false,
                    'min_lead_time_hours' => 48,
                    'emergency_lead_time_hours' => 0,
                    'require_rollback_plan' => true,
                    'require_test_plan' => $org->slug === 'wayne-enterprises',
                    'require_implementation_plan' => true,
                    'allow_customer_submit' => true,
                    'auto_approve_standard' => $org->slug === 'acme-corp',
                    'change_window_notes' => 'Preferred maintenance window: Saturday 2:00 AM - 6:00 AM CT',
                ]
            );

            // Create change categories per org
            $categories = [
                [
                    'name' => 'Server Maintenance',
                    'description' => 'OS patches, reboots, hardware upgrades',
                    'default_type' => 'standard',
                    'default_risk_level' => 'low',
                    'template_implementation_plan' => "1. Notify affected users\n2. Back up current state\n3. Apply patches/changes\n4. Verify services restored\n5. Confirm with requester",
                    'template_rollback_plan' => "1. Restore from backup\n2. Revert patches if applicable\n3. Verify services restored",
                    'cab_required' => false,
                ],
                [
                    'name' => 'Network Configuration',
                    'description' => 'Firewall rules, VLAN changes, DNS updates',
                    'default_type' => 'normal',
                    'default_risk_level' => 'medium',
                    'template_implementation_plan' => "1. Document current config\n2. Apply changes in maintenance window\n3. Test connectivity\n4. Monitor for 30 minutes",
                    'template_rollback_plan' => "1. Revert to documented config\n2. Verify connectivity restored",
                    'cab_required' => true,
                ],
                [
                    'name' => 'Software Deployment',
                    'description' => 'Application installs, upgrades, removals',
                    'default_type' => 'normal',
                    'default_risk_level' => 'medium',
                    'template_implementation_plan' => "1. Test in staging environment\n2. Create system restore point\n3. Deploy to production\n4. Verify functionality\n5. User acceptance testing",
                    'template_rollback_plan' => "1. Uninstall new version\n2. Restore previous version from backup\n3. Verify functionality",
                    'cab_required' => true,
                ],
                [
                    'name' => 'User Access Change',
                    'description' => 'Permission changes, group membership, account modifications',
                    'default_type' => 'standard',
                    'default_risk_level' => 'low',
                    'cab_required' => false,
                ],
                [
                    'name' => 'Infrastructure Migration',
                    'description' => 'Server migrations, cloud transitions, data center moves',
                    'default_type' => 'normal',
                    'default_risk_level' => 'high',
                    'template_implementation_plan' => "1. Full backup of source systems\n2. Pre-migration testing\n3. Execute migration per runbook\n4. Post-migration validation\n5. DNS cutover\n6. Monitor for 24 hours",
                    'template_rollback_plan' => "1. DNS revert to source\n2. Restore from backup if needed\n3. Validate source systems operational",
                    'template_test_plan' => "1. Verify all services accessible\n2. Test authentication flows\n3. Confirm data integrity\n4. Performance benchmarking",
                    'cab_required' => true,
                ],
            ];

            foreach ($categories as $cat) {
                ChangeCategory::firstOrCreate(
                    ['organization_id' => $org->id, 'name' => $cat['name']],
                    array_merge($cat, ['organization_id' => $org->id, 'is_active' => true])
                );
            }

            // Add CAB members (MSP admin + tech for each org)
            if ($admin) {
                CabMember::firstOrCreate(
                    ['organization_id' => $org->id, 'user_id' => $admin->id],
                    ['role' => 'chair', 'is_active' => true]
                );
            }
            if ($tech) {
                CabMember::firstOrCreate(
                    ['organization_id' => $org->id, 'user_id' => $tech->id],
                    ['role' => 'member', 'is_active' => true]
                );
            }

            // Add customer admin to CAB if they exist
            $customerAdmin = $org->users()->whereHas('roles', fn ($q) => $q->where('name', 'customer_admin'))->first();
            if ($customerAdmin) {
                CabMember::firstOrCreate(
                    ['organization_id' => $org->id, 'user_id' => $customerAdmin->id],
                    ['role' => 'member', 'is_active' => true]
                );
            }

            // Add a sample blackout period for one org
            if ($org->slug === 'acme-corp') {
                ChangeBlackoutPeriod::firstOrCreate(
                    ['organization_id' => $org->id, 'name' => 'Q1 End Freeze'],
                    [
                        'reason' => 'Quarter-end processing - no changes allowed except emergencies',
                        'starts_at' => now()->endOfMonth()->subDays(2)->startOfDay(),
                        'ends_at' => now()->endOfMonth()->endOfDay(),
                        'allow_emergency' => true,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
