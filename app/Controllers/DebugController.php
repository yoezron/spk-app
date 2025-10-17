<?php

/**
 * Auth Debug Checker
 * 
 * Temporary debug file untuk checking:
 * 1. User authentication status
 * 2. User groups/roles
 * 3. Redirect logic
 * 
 * CARA PAKAI:
 * 1. Simpan file ini di: app/Controllers/DebugController.php
 * 2. Akses via: /debug (setelah login)
 * 3. Lihat output untuk diagnostic
 * 4. HAPUS file ini setelah selesai debugging!
 * 
 * @package App\Controllers
 * @temporary FOR DEBUGGING ONLY
 */

namespace App\Controllers;

class DebugController extends BaseController
{
    public function index()
    {
        // SECURITY: Only allow in development mode
        if (ENVIRONMENT !== 'development') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $html = '<html><head><title>Auth Debug</title>';
        $html .= '<style>
            body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
            .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
            h2 { color: #555; margin-top: 30px; background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; }
            .info-block { background: #e7f3ff; border: 1px solid #b3d7ff; padding: 15px; margin: 10px 0; border-radius: 4px; }
            .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
            .warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
            .danger { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
            pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; }
            .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; margin: 2px; }
            .badge-success { background: #28a745; color: white; }
            .badge-warning { background: #ffc107; color: #333; }
            .badge-danger { background: #dc3545; color: white; }
            .badge-info { background: #17a2b8; color: white; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
            th { background: #f8f9fa; font-weight: bold; }
            .code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #e83e8c; }
        </style></head><body><div class="container">';

        $html .= '<h1>🔍 SPK Authentication Debug Report</h1>';
        $html .= '<p><em>Generated: ' . date('Y-m-d H:i:s') . '</em></p>';

        // 1. Check Auth Status
        $html .= '<h2>1️⃣ Authentication Status</h2>';

        if (auth()->loggedIn()) {
            $html .= '<div class="info-block success"><strong>✅ Status:</strong> LOGGED IN</div>';

            $user = auth()->user();

            $html .= '<table>';
            $html .= '<tr><th>Property</th><th>Value</th></tr>';
            $html .= '<tr><td><strong>User ID</strong></td><td>' . ($user->id ?? 'N/A') . '</td></tr>';
            $html .= '<tr><td><strong>Email</strong></td><td>' . ($user->email ?? 'N/A') . '</td></tr>';
            $html .= '<tr><td><strong>Username</strong></td><td>' . ($user->username ?? 'N/A') . '</td></tr>';
            $html .= '<tr><td><strong>Email Verified</strong></td><td>' . ($user->email_verified_at ? '✅ Yes' : '❌ No') . '</td></tr>';
            $html .= '<tr><td><strong>Status</strong></td><td>' . ($user->status ?? 'N/A') . '</td></tr>';
            $html .= '</table>';
        } else {
            $html .= '<div class="info-block danger"><strong>❌ Status:</strong> NOT LOGGED IN</div>';
            $html .= '<p>Please <a href="' . base_url('/login') . '">login</a> first to see full debug info.</p>';
            $html .= '</div></body></html>';
            return $html;
        }

        // 2. Check Groups/Roles
        $html .= '<h2>2️⃣ User Groups (Roles)</h2>';

        $groups = $user->getGroups();

        if (!empty($groups)) {
            $html .= '<div class="info-block">';
            $html .= '<strong>Assigned Groups:</strong><br>';
            foreach ($groups as $group) {
                $html .= '<span class="badge badge-info">' . htmlspecialchars($group) . '</span>';
            }
            $html .= '</div>';

            // Test each role
            $html .= '<table>';
            $html .= '<tr><th>Role Check</th><th>Method</th><th>Result</th></tr>';

            $rolesToCheck = ['superadmin', 'pengurus', 'koordinator', 'anggota', 'calon_anggota'];
            foreach ($rolesToCheck as $role) {
                $result = $user->inGroup($role);
                $badge = $result ? '<span class="badge badge-success">✅ TRUE</span>' : '<span class="badge badge-danger">❌ FALSE</span>';
                $html .= '<tr><td><code>' . $role . '</code></td><td><code>$user->inGroup(\'' . $role . '\')</code></td><td>' . $badge . '</td></tr>';
            }
            $html .= '</table>';
        } else {
            $html .= '<div class="info-block warning"><strong>⚠️ WARNING:</strong> User has NO groups assigned!</div>';
        }

        // 3. Redirect Logic Test
        $html .= '<h2>3️⃣ Redirect Logic Test</h2>';

        $html .= '<div class="info-block">';
        $html .= '<table>';
        $html .= '<tr><th>Condition</th><th>URL</th><th>Expected</th></tr>';

        if ($user->inGroup('superadmin')) {
            $expectedUrl = '/super/dashboard';
            $html .= '<tr><td><code>inGroup(\'superadmin\')</code></td><td><span class="code">' . $expectedUrl . '</span></td><td><span class="badge badge-success">SHOULD REDIRECT HERE</span></td></tr>';
        } else {
            $html .= '<tr><td><code>inGroup(\'superadmin\')</code></td><td>-</td><td><span class="badge badge-danger">NO MATCH</span></td></tr>';
        }

        if ($user->inGroup('pengurus')) {
            $expectedUrl = '/admin/dashboard';
            $html .= '<tr><td><code>inGroup(\'pengurus\')</code></td><td><span class="code">' . $expectedUrl . '</span></td><td><span class="badge badge-success">SHOULD REDIRECT HERE</span></td></tr>';
        } else {
            $html .= '<tr><td><code>inGroup(\'pengurus\')</code></td><td>-</td><td><span class="badge badge-danger">NO MATCH</span></td></tr>';
        }

        if ($user->inGroup('koordinator')) {
            $expectedUrl = '/admin/dashboard';
            $html .= '<tr><td><code>inGroup(\'koordinator\')</code></td><td><span class="code">' . $expectedUrl . '</span></td><td><span class="badge badge-success">SHOULD REDIRECT HERE</span></td></tr>';
        } else {
            $html .= '<tr><td><code>inGroup(\'koordinator\')</code></td><td>-</td><td><span class="badge badge-danger">NO MATCH</span></td></tr>';
        }

        if ($user->inGroup('anggota')) {
            $expectedUrl = '/member/dashboard';
            $html .= '<tr><td><code>inGroup(\'anggota\')</code></td><td><span class="code">' . $expectedUrl . '</span></td><td><span class="badge badge-success">SHOULD REDIRECT HERE</span></td></tr>';
        } else {
            $html .= '<tr><td><code>inGroup(\'anggota\')</code></td><td>-</td><td><span class="badge badge-danger">NO MATCH</span></td></tr>';
        }

        if ($user->inGroup('calon_anggota')) {
            $expectedUrl = '/member/dashboard';
            $html .= '<tr><td><code>inGroup(\'calon_anggota\')</code></td><td><span class="code">' . $expectedUrl . '</span></td><td><span class="badge badge-success">SHOULD REDIRECT HERE</span></td></tr>';
        } else {
            $html .= '<tr><td><code>inGroup(\'calon_anggota\')</code></td><td>-</td><td><span class="badge badge-danger">NO MATCH</span></td></tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        // 4. Database Check
        $html .= '<h2>4️⃣ Database Groups Check</h2>';

        try {
            $db = \Config\Database::connect();

            // Get user's groups from database
            $query = $db->query("
                SELECT ag.id, ag.title, ag.description 
                FROM auth_groups ag
                INNER JOIN auth_groups_users agu ON agu.group = ag.title
                WHERE agu.user_id = ?
            ", [$user->id]);

            $dbGroups = $query->getResult();

            if ($dbGroups) {
                $html .= '<div class="info-block success">';
                $html .= '<strong>✅ Groups from Database:</strong><br>';
                $html .= '<table>';
                $html .= '<tr><th>ID</th><th>Title</th><th>Description</th></tr>';
                foreach ($dbGroups as $group) {
                    $html .= '<tr>';
                    $html .= '<td>' . $group->id . '</td>';
                    $html .= '<td><strong>' . htmlspecialchars($group->title) . '</strong></td>';
                    $html .= '<td>' . htmlspecialchars($group->description ?? 'N/A') . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</table>';
                $html .= '</div>';
            } else {
                $html .= '<div class="info-block danger"><strong>❌ ERROR:</strong> No groups found in database for this user!</div>';
            }
        } catch (\Exception $e) {
            $html .= '<div class="info-block danger"><strong>❌ DATABASE ERROR:</strong> ' . $e->getMessage() . '</div>';
        }

        // 5. Permissions Check
        $html .= '<h2>5️⃣ Permissions Check (Sample)</h2>';

        $permissionsToCheck = [
            'member.view',
            'member.manage',
            'role.manage',
            'permission.manage',
            'master.manage',
        ];

        $html .= '<table>';
        $html .= '<tr><th>Permission</th><th>Has Permission?</th></tr>';

        foreach ($permissionsToCheck as $permission) {
            $hasPerm = $user->can($permission);
            $badge = $hasPerm ? '<span class="badge badge-success">✅ YES</span>' : '<span class="badge badge-danger">❌ NO</span>';
            $html .= '<tr><td><code>' . $permission . '</code></td><td>' . $badge . '</td></tr>';
        }

        $html .= '</table>';

        // 6. Helper Functions Test
        $html .= '<h2>6️⃣ Helper Functions Test</h2>';

        $html .= '<table>';
        $html .= '<tr><th>Helper Function</th><th>Result</th></tr>';

        helper('auth');

        $html .= '<tr><td><code>is_super_admin()</code></td><td>' . (is_super_admin() ? '<span class="badge badge-success">✅ TRUE</span>' : '<span class="badge badge-danger">❌ FALSE</span>') . '</td></tr>';
        $html .= '<tr><td><code>is_admin()</code></td><td>' . (is_admin() ? '<span class="badge badge-success">✅ TRUE</span>' : '<span class="badge badge-danger">❌ FALSE</span>') . '</td></tr>';
        $html .= '<tr><td><code>is_member()</code></td><td>' . (is_member() ? '<span class="badge badge-success">✅ TRUE</span>' : '<span class="badge badge-danger">❌ FALSE</span>') . '</td></tr>';
        $html .= '<tr><td><code>user_role()</code></td><td><span class="badge badge-info">' . user_role() . '</span></td></tr>';
        $html .= '<tr><td><code>user_dashboard_url()</code></td><td><span class="code">' . user_dashboard_url() . '</span></td></tr>';

        $html .= '</table>';

        // 7. Recommendations
        $html .= '<h2>7️⃣ Diagnostic Summary & Recommendations</h2>';

        $html .= '<div class="info-block">';

        // Analyze the situation
        $issues = [];
        $solutions = [];

        if (empty($groups)) {
            $issues[] = '❌ User has NO groups assigned';
            $solutions[] = 'Run seeder or manually assign user to a group in database';
        }

        if (!$user->inGroup('superadmin') && !$user->inGroup('pengurus') && !$user->inGroup('koordinator') && !$user->inGroup('anggota') && !$user->inGroup('calon_anggota')) {
            $issues[] = '❌ User group name mismatch';
            $solutions[] = 'Check auth_groups table - role names should be: superadmin, pengurus, koordinator, anggota, calon_anggota (lowercase, no spaces)';
        }

        if (!$user->email_verified_at) {
            $issues[] = '⚠️ Email not verified';
            $solutions[] = 'Verify email or disable email verification requirement';
        }

        if ($issues) {
            $html .= '<h3 style="color: #dc3545;">🚨 Issues Found:</h3>';
            $html .= '<ul>';
            foreach ($issues as $issue) {
                $html .= '<li>' . $issue . '</li>';
            }
            $html .= '</ul>';

            $html .= '<h3 style="color: #007bff;">💡 Recommended Solutions:</h3>';
            $html .= '<ol>';
            foreach ($solutions as $solution) {
                $html .= '<li>' . $solution . '</li>';
            }
            $html .= '</ol>';
        } else {
            $html .= '<div class="success" style="padding: 20px; text-align: center; font-size: 18px;">';
            $html .= '<strong>✅ Everything looks good!</strong><br>';
            $html .= 'User should be able to access dashboard correctly.';
            $html .= '</div>';
        }

        $html .= '</div>';

        // 8. Quick Fixes
        $html .= '<h2>8️⃣ Quick Fix SQL Queries</h2>';

        $html .= '<div class="info-block warning">';
        $html .= '<p><strong>⚠️ If user cannot access dashboard, try these SQL queries:</strong></p>';

        $html .= '<h4>1. Check current groups:</h4>';
        $html .= '<pre>SELECT * FROM auth_groups;</pre>';

        $html .= '<h4>2. Check user group assignments:</h4>';
        $html .= '<pre>SELECT u.id, u.email, ag.title as role 
FROM users u
LEFT JOIN auth_groups_users agu ON agu.user_id = u.id
LEFT JOIN auth_groups ag ON ag.title = agu.group
WHERE u.id = ' . $user->id . ';</pre>';

        $html .= '<h4>3. Assign user to superadmin group (if needed):</h4>';
        $html .= '<pre>-- First, ensure group exists
INSERT IGNORE INTO auth_groups (title, description) 
VALUES (\'superadmin\', \'Super Administrator\');

-- Then assign user
INSERT INTO auth_groups_users (user_id, group, created_at) 
VALUES (' . $user->id . ', \'superadmin\', NOW());</pre>';

        $html .= '<h4>4. Fix group names (if they have spaces or wrong case):</h4>';
        $html .= '<pre>-- Update group titles to lowercase
UPDATE auth_groups SET title = LOWER(REPLACE(title, \' \', \'\'));

-- Update user group assignments
UPDATE auth_groups_users SET group = LOWER(REPLACE(group, \' \', \'\'));</pre>';

        $html .= '</div>';

        // 9. Test Links
        $html .= '<h2>9️⃣ Test Dashboard Access</h2>';

        $html .= '<div class="info-block">';
        $html .= '<p><strong>Click these links to test dashboard access:</strong></p>';
        $html .= '<ul>';
        $html .= '<li><a href="' . base_url('/super/dashboard') . '" target="_blank">🔗 Super Admin Dashboard (/super/dashboard)</a></li>';
        $html .= '<li><a href="' . base_url('/admin/dashboard') . '" target="_blank">🔗 Admin Dashboard (/admin/dashboard)</a></li>';
        $html .= '<li><a href="' . base_url('/member/dashboard') . '" target="_blank">🔗 Member Dashboard (/member/dashboard)</a></li>';
        $html .= '<li><a href="' . base_url('/') . '" target="_blank">🔗 Home Page (/)</a></li>';
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '<hr style="margin: 40px 0;">';
        $html .= '<p style="text-align: center; color: #666;"><em>⚠️ REMEMBER: Delete this debug controller after fixing the issue!</em></p>';

        $html .= '</div></body></html>';

        return $html;
    }
}
