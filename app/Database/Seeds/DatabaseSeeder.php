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
 * @version 1.0.0
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
        echo "  - Role-Permission Mappings\n";
        echo "  - Super Admin Account\n";
        echo "  - Provinces (38 records)\n";
        echo "  - Master Data (36 records)\n";
        echo "\n";
        echo "════════════════════════════════════════════════════════\n\n";

        // 1. Roles (MUST BE FIRST)
        echo "[1/6] Running RolesSeeder...\n";
        $this->call('RolesSeeder');

        // 2. Permissions (MUST BE SECOND)
        echo "[2/6] Running PermissionsSeeder...\n";
        $this->call('PermissionsSeeder');

        // 3. Role-Permission Mappings (MUST BE THIRD)
        echo "[3/6] Running RolePermissionsSeeder...\n";
        $this->call('RolePermissionsSeeder');

        // 4. Super Admin (Can be run after roles exist)
        echo "[4/6] Running SuperAdminSeeder...\n";
        $this->call('SuperAdminSeeder');

        // 5. Provinsi (Independent)
        echo "[5/6] Running ProvinsiSeeder...\n";
        $this->call('ProvinsiSeeder');

        // 6. Master Data (Independent)
        echo "[6/6] Running MasterDataSeeder...\n";
        $this->call('MasterDataSeeder');

        // Final Summary
        echo "\n";
        echo "╔════════════════════════════════════════════════════════╗\n";
        echo "║              SEEDING COMPLETED SUCCESSFULLY!            ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "📊 Database Seeding Summary:\n";
        echo "   ✓ Roles: 5\n";
        echo "   ✓ Permissions: 55\n";
        echo "   ✓ Role-Permission Mappings: ~150\n";
        echo "   ✓ Super Admin: 1\n";
        echo "   ✓ Provinces: 38\n";
        echo "   ✓ Master Data: 36\n";
        echo "\n";
        echo "🔐 Super Admin Credentials:\n";
        echo "   Email    : admin@spk.or.id\n";
        echo "   Username : superadmin\n";
        echo "   Password : SuperAdmin123!\n";
        echo "\n";
        echo "⚠️  IMPORTANT: Change the Super Admin password immediately!\n";
        echo "\n";
        echo "🎯 Next Steps:\n";
        echo "   1. Login to admin panel\n";
        echo "   2. Change Super Admin password\n";
        echo "   3. Configure email settings (.env)\n";
        echo "   4. Import 1,773 existing members (bulk import)\n";
        echo "   5. Test RBAC permissions\n";
        echo "\n";
        echo "════════════════════════════════════════════════════════\n";
        echo "Ready to start! 🚀\n";
        echo "════════════════════════════════════════════════════════\n\n";
    }
}
