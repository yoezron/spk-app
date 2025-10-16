<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder
 * 
 * Master seeder untuk menjalankan semua seeders secara berurutan
 * Jalankan dengan: php spark db:seed DatabaseSeeder
 * 
 * @package App\Database\Seeds
 * @author  SPK Development Team
 * @version 1.1.0 (Updated: Added University & Study Program seeders)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║     SISTEM INFORMASI ANGGOTA SPK - DATABASE SEEDER     ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "Starting database seeding process...\n";
        echo "This will initialize:\n";
        echo "  - Roles (5 records)\n";
        echo "  - Permissions (55 records)\n";
        echo "  - Role-Permission Mappings (~150 records)\n";
        echo "  - Super Admin Account (1 record)\n";
        echo "  - Provinces (38 records)\n";
        echo "  - Employment Statuses (13 records)\n";
        echo "  - Salary Ranges (10 records)\n";
        echo "  - Universities (100+ records) 🆕\n";
        echo "  - Study Programs (100+ records) 🆕\n";
        echo "\n";
        echo "════════════════════════════════════════════════════════\n\n";

        $startTime = microtime(true);

        // ===========================================
        // PHASE 1: RBAC FOUNDATION
        // ===========================================
        echo "🔐 PHASE 1: RBAC Foundation\n";
        echo "─────────────────────────────────────────\n";

        // 1. Roles (MUST BE FIRST)
        echo "[1/9] Running RolesSeeder...\n";
        $this->call('RolesSeeder');

        // 2. Permissions (MUST BE SECOND)
        echo "[2/9] Running PermissionsSeeder...\n";
        $this->call('PermissionsSeeder');

        // 3. Role-Permission Mappings (MUST BE THIRD)
        echo "[3/9] Running RolePermissionsSeeder...\n";
        $this->call('RolePermissionsSeeder');

        // 4. Super Admin (Can be run after roles exist)
        echo "[4/9] Running SuperAdminSeeder...\n";
        $this->call('SuperAdminSeeder');

        echo "\n";

        // ===========================================
        // PHASE 2: MASTER DATA - GEOGRAPHIC
        // ===========================================
        echo "📍 PHASE 2: Geographic Master Data\n";
        echo "─────────────────────────────────────────\n";

        // 5. Provinsi (Independent - must run before Universities)
        echo "[5/9] Running ProvinsiSeeder...\n";
        $this->call('ProvinsiSeeder');

        echo "\n";

        // ===========================================
        // PHASE 3: MASTER DATA - EMPLOYMENT
        // ===========================================
        echo "💼 PHASE 3: Employment Master Data\n";
        echo "─────────────────────────────────────────\n";

        // 6. Master Data (Employment Statuses & Salary Ranges)
        echo "[6/9] Running MasterDataSeeder...\n";
        $this->call('MasterDataSeeder');

        echo "\n";

        // ===========================================
        // PHASE 4: MASTER DATA - EDUCATION (NEW!)
        // ===========================================
        echo "🎓 PHASE 4: Education Master Data (NEW!)\n";
        echo "─────────────────────────────────────────\n";

        // 7. Universities (requires provinces - FK dependency)
        echo "[7/9] Running UniversitySeeder...\n";
        try {
            $this->call('UniversitySeeder');
        } catch (\Exception $e) {
            echo "  ⚠️ Warning: UniversitySeeder failed - {$e->getMessage()}\n";
            echo "  → Skipping Universities (will need manual seeding)\n";
        }

        // 8. Study Programs (independent)
        echo "[8/9] Running StudyProgramSeeder...\n";
        try {
            $this->call('StudyProgramSeeder');
        } catch (\Exception $e) {
            echo "  ⚠️ Warning: StudyProgramSeeder failed - {$e->getMessage()}\n";
            echo "  → Skipping Study Programs (will need manual seeding)\n";
        }

        echo "\n";

        // ===========================================
        // PHASE 5: MENUS (OPTIONAL - if exists)
        // ===========================================
        echo "📱 PHASE 5: Dynamic Menu Data (Optional)\n";
        echo "─────────────────────────────────────────\n";

        // 9. Menus (if MenuSeeder exists)
        echo "[9/9] Running MenuSeeder (if exists)...\n";
        try {
            $this->call('MenuSeeder');
        } catch (\Exception $e) {
            echo "  → MenuSeeder not found or failed, skipping\n";
        }

        echo "\n";

        // ===========================================
        // COMPLETION SUMMARY
        // ===========================================
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║              SEEDING COMPLETED SUCCESSFULLY!            ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";

        // Get actual counts from database
        $counts = $this->getDatabaseCounts();

        echo "📊 Database Seeding Summary:\n";
        echo "─────────────────────────────────────────\n";
        echo sprintf("   ✓ Roles                 : %4d\n", $counts['roles']);
        echo sprintf("   ✓ Permissions           : %4d\n", $counts['permissions']);
        echo sprintf("   ✓ Role-Permission Maps  : %4d\n", $counts['role_permissions']);
        echo sprintf("   ✓ Super Admin           : %4d\n", $counts['users']);
        echo sprintf("   ✓ Provinces             : %4d\n", $counts['provinces']);
        echo sprintf("   ✓ Employment Statuses   : %4d\n", $counts['employment_statuses']);
        echo sprintf("   ✓ Salary Ranges         : %4d\n", $counts['salary_ranges']);
        echo sprintf("   ✓ Universities          : %4d 🆕\n", $counts['universities']);
        echo sprintf("   ✓ Study Programs        : %4d 🆕\n", $counts['study_programs']);
        echo "─────────────────────────────────────────\n";
        echo "   Execution Time          : {$duration} seconds\n";
        echo "─────────────────────────────────────────\n";
        echo "\n";

        echo "🔐 Super Admin Credentials:\n";
        echo "─────────────────────────────────────────\n";
        echo "   Email    : admin@spk.or.id\n";
        echo "   Username : superadmin\n";
        echo "   Password : SuperAdmin123!\n";
        echo "─────────────────────────────────────────\n";
        echo "   ⚠️  IMPORTANT: Change the Super Admin password immediately!\n";
        echo "\n";

        echo "🎯 Next Steps:\n";
        echo "─────────────────────────────────────────\n";
        echo "   1. Login to admin panel\n";
        echo "   2. Change Super Admin password\n";
        echo "   3. Configure email settings (.env)\n";
        echo "   4. Verify master data (provinces, universities, etc)\n";
        echo "   5. Import 1,775 existing members (bulk import)\n";
        echo "   6. Test RBAC permissions\n";
        echo "\n";
        echo "════════════════════════════════════════════════════════\n";
        echo "Ready to start! 🚀\n";
        echo "════════════════════════════════════════════════════════\n\n";
    }

    /**
     * Get actual record counts from database
     * 
     * @return array
     */
    private function getDatabaseCounts(): array
    {
        return [
            'roles' => $this->db->table('auth_groups')->countAllResults(),
            'permissions' => $this->db->table('auth_permissions')->countAllResults(),
            'role_permissions' => $this->db->table('auth_groups_permissions')->countAllResults(),
            'users' => $this->db->table('users')->countAllResults(),
            'provinces' => $this->db->table('provinces')->countAllResults(),
            'employment_statuses' => $this->getTableCount('employment_statuses'),
            'salary_ranges' => $this->getTableCount('salary_ranges'),
            'universities' => $this->getTableCount('universities'),
            'study_programs' => $this->getTableCount('study_programs'),
        ];
    }

    /**
     * Helper to get table count with error handling
     * 
     * @param string $table
     * @return int
     */
    private function getTableCount(string $table): int
    {
        try {
            return $this->db->table($table)->countAllResults();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
