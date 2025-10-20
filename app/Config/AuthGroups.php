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
        'member.import' => 'Can import bulk members',
        'member.view'   => 'Can view members',
        'member.edit'   => 'Can edit members',
        'member.delete' => 'Can delete members',
        'member.approve' => 'Can approve members',
        'member.export' => 'Can export members',
        // Add more permissions as needed
    ];

    public array $matrix = [
        'superadmin' => [
            'member.*',  // All member permissions
            'admin.*',   // All admin permissions
        ],
        'pengurus' => [
            'member.view',
            'member.edit',
            'member.import',
            'member.export',
        ],
    ];
}
