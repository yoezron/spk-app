<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public string $defaultGroup = 'user';

    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Complete control of the site.',
        ],
        'admin' => [
            'title'       => 'Admin',
            'description' => 'Day to day administrators of the site.',
        ],
        'pengurus' => [
            'title'       => 'Pengurus',
            'description' => 'SPK administrators.',
        ],
        'anggota' => [
            'title'       => 'Anggota',
            'description' => 'SPK members.',
        ],
        'calon_anggota' => [
            'title'       => 'Calon Anggota',
            'description' => 'Pending SPK members.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'General users.',
        ],
    ];

    public array $permissions = [
        // Member management
        'member.import' => 'Can import bulk members',
        'member.view'   => 'Can view members',
        'member.edit'   => 'Can edit members',
        'member.delete' => 'Can delete members',
        'member.approve' => 'Can approve members',
        'member.export' => 'Can export members',
        'member.manage' => 'Can manage members',

        // Admin dashboard
        'admin.dashboard' => 'Can access admin dashboard',
        'dashboard.admin' => 'Can access admin dashboard (alias)',

        // Payment management
        'payment.view' => 'Can view payments',
        'payment.verify' => 'Can verify payments',
        'payment.report' => 'Can view payment reports',
        'payment.export' => 'Can export payment data',

        // Complaint/Ticket management
        'complaint.view' => 'Can view complaints',
        'complaint.manage' => 'Can manage complaints',
        'ticket.view' => 'Can view tickets (alias for complaint.view)',

        // Content management
        'content.manage' => 'Can manage content (posts/pages)',
        'content.create' => 'Can create content',

        // Forum moderation
        'forum.moderate' => 'Can moderate forum',

        // Organization structure
        'org_structure.view' => 'Can view organization structure',
        'org_structure.manage' => 'Can manage organization structure',
        'org_structure.assign' => 'Can assign organization positions',

        // Statistics
        'statistics.view' => 'Can view statistics',

        // Survey management
        'survey.manage' => 'Can manage surveys',
        'survey.create' => 'Can create surveys',
        'survey.view_results' => 'Can view survey results',

        // WhatsApp Group management
        'wagroup.manage' => 'Can manage WhatsApp groups',
        'wa_group.manage' => 'Can manage WhatsApp groups (alias)',
    ];

    public array $matrix = [
        'superadmin' => [
            'admin.*',          // All admin permissions
            'dashboard.*',      // All dashboard permissions (alias)
            'member.*',         // All member permissions
            'payment.*',        // All payment permissions
            'complaint.*',      // All complaint permissions
            'ticket.*',         // All ticket permissions (alias for complaint)
            'content.*',        // All content permissions
            'forum.*',          // All forum permissions
            'org_structure.*',  // All organization structure permissions
            'statistics.*',     // All statistics permissions
            'survey.*',         // All survey permissions
            'wagroup.*',        // All WhatsApp group permissions
            'wa_group.*',       // All WhatsApp group permissions (alias)
        ],
        'pengurus' => [
            'admin.dashboard',
            'dashboard.admin',
            'member.view',
            'member.edit',
            'member.import',
            'member.export',
            'member.approve',
            'payment.view',
            'payment.verify',
            'complaint.view',
            'complaint.manage',
            'ticket.view',
            'content.manage',
            'content.create',
            'forum.moderate',
            'statistics.view',
            'survey.manage',
            'survey.create',
            'survey.view_results',
            'wagroup.manage',
            'wa_group.manage',
        ],
        'koordinator' => [
            'admin.dashboard',
            'member.view',
            'member.approve',
            'payment.view',
            'complaint.view',
            'statistics.view',
        ],
    ];
}
