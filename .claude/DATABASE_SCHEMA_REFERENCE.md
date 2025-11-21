# Referensi Struktur Database SPK

> **PENTING untuk Claude AI**: Dokumen ini adalah referensi struktur database yang HARUS dipahami sebelum melakukan perbaikan atau modifikasi kode apapun.

---

## 📋 TABEL DARI CODEIGNITER SHIELD (Authentication)

Aplikasi ini menggunakan **CodeIgniter Shield** untuk authentication. Tabel-tabel berikut **SUDAH ADA** dan dikelola oleh Shield:

### 1. `users` (Table Utama User)
**Tabel ini dikelola oleh Shield, BUKAN custom migration!**

Kolom standar Shield:
- `id` INT (PK)
- `username` VARCHAR(30) UNIQUE
- `status` VARCHAR(255) - status akun (active, banned, etc)
- `status_message` VARCHAR(255)
- `active` TINYINT(1) - flag aktif/non-aktif
- `last_active` DATETIME
- `created_at` DATETIME
- `updated_at` DATETIME
- `deleted_at` DATETIME (soft delete)

Kolom tambahan dari migration `2025_01_15_000001`:
- `activation_token` VARCHAR(255) - untuk aktivasi member import
- `activation_token_expires_at` DATETIME
- `activated_at` DATETIME

**PENTING**:
- Email dan password **TIDAK** disimpan di tabel `users`!
- Email disimpan di tabel `auth_identities`
- Password (hashed) juga disimpan di `auth_identities`

---

### 2. `auth_identities` (Email, Password, Token)
**Tabel untuk menyimpan credentials dan identitas autentikasi**

Kolom utama:
- `id` INT (PK)
- `user_id` INT (FK ke `users.id`)
- `type` VARCHAR(255) - tipe identity:
  - `'email_password'` - untuk login email/password
  - `'access_token'` - untuk API token
  - `'magic-link'` - untuk magic link login
- `name` VARCHAR(255) - nama/label identity (nullable)
- `secret` VARCHAR(255) - berisi:
  - **Email address** (untuk type = 'email_password')
  - **Password hash** (untuk type = 'email_password' di kolom terpisah)
  - **Token** (untuk type = 'access_token')
- `secret2` VARCHAR(255) - password hash (untuk email_password)
- `expires` DATETIME - waktu kadaluarsa (untuk token)
- `extra` TEXT - data tambahan (JSON)
- `force_reset` TINYINT(1) - flag paksa reset password
- `last_used_at` DATETIME
- `created_at` DATETIME
- `updated_at` DATETIME

**CARA MENDAPATKAN EMAIL USER**:
```php
// BENAR - Join dengan auth_identities
$this->db->table('users')
    ->select('users.*, auth_identities.secret as email')
    ->join('auth_identities', 'auth_identities.user_id = users.id')
    ->where('auth_identities.type', 'email_password')
    ->get();

// SALAH - users tidak punya kolom email!
$this->db->table('users')
    ->select('email') // ❌ KOLOM INI TIDAK ADA!
    ->get();
```

**Lihat contoh di UserModel.php baris 182-188**:
```php
public function findByEmail(string $email)
{
    return $this->select('users.*')
        ->join('auth_identities', 'auth_identities.user_id = users.id')
        ->where('auth_identities.type', 'email_password')
        ->where('auth_identities.secret', $email)
        ->first();
}
```

---

### 3. `auth_groups_users` (User Roles/Groups)
**Pivot table untuk relasi many-to-many users dengan groups/roles**

Kolom:
- `id` INT (PK)
- `user_id` INT (FK ke `users.id`)
- `group` VARCHAR(255) - nama role/group:
  - `'Super Admin'`
  - `'Pengurus'`
  - `'Koordinator Wilayah'`
  - `'Anggota'`
  - `'Calon Anggota'`
- `created_at` DATETIME

**PENTING**:
- Satu user biasanya hanya punya 1 role (meskipun strukturnya many-to-many)
- Role disimpan sebagai string di kolom `group`, bukan ID!

---

### 4. `auth_permissions_users` (User Permissions)
**Direct permissions untuk user tertentu (bypass group permissions)**

Kolom:
- `id` INT (PK)
- `user_id` INT (FK ke `users.id`)
- `permission` VARCHAR(255) - nama permission
- `created_at` DATETIME

---

### 5. `auth_logins` (Login Attempts Log)
**Log semua percobaan login**

Kolom:
- `id` INT (PK)
- `ip_address` VARCHAR(255)
- `user_agent` TEXT
- `id_type` VARCHAR(255) - email/username
- `identifier` VARCHAR(255) - nilai email/username
- `user_id` INT (FK, nullable)
- `date` DATETIME
- `success` TINYINT(1)

---

### 6. `auth_token_logins` (API Token Login Attempts)
**Log login menggunakan API token**

---

### 7. `auth_remember_tokens` (Remember Me Tokens)
**Token untuk "remember me" functionality**

---

## 📋 TABEL CUSTOM SPK

### 8. `member_profiles` (Profil Anggota)
**RELASI 1-to-1 dengan `users` via `user_id`**

Foreign Keys:
- `user_id` → `users.id` (CASCADE DELETE)
- `province_id` → `provinces.id`
- `regency_id` → `regencies.id`
- `university_id` → `universities.id`
- `study_program_id` → `study_programs.id`
- `employment_status_id` → `employment_statuses.id`
- `salary_range_id` → `salary_ranges.id`
- `region_id` → `regions.id`
- `verified_by` → `users.id` (admin yang verify)
- `approved_by` → `users.id` (admin yang approve)

Kolom penting:
- `user_id` INT (FK ke `users.id`, UNIQUE)
- `member_number` VARCHAR(50) UNIQUE - format: SPK-2025-00001
- `full_name` VARCHAR(255)
- `nik` VARCHAR(20) - NIK KTP
- `nidn_nip` VARCHAR(30) - NIDN/NIP dosen
- `membership_status` ENUM: pending, active, inactive, suspended, expired
- `join_date` DATE
- `verified_at` DATETIME
- `verified_by` INT
- `district_id` INT - **KOLOM INI TIDAK ADA DI MIGRATION!** (bug?)
- `village_id` INT - **KOLOM INI TIDAK ADA DI MIGRATION!** (bug?)

**CATATAN PENTING**:
- Di MemberProfileModel.php line 39-40 ada `district_id` dan `village_id` di allowedFields
- Tapi di migration TIDAK ADA kolom ini!
- Ini bisa menyebabkan error saat save jika ada data district/village

---

### 9. `provinces`, `regencies` (Data Geografis)
Master data provinsi dan kabupaten/kota Indonesia

---

### 10. `universities`, `study_programs` (Data Kampus)
Master data universitas dan program studi

---

### 11. `employment_statuses` (Status Kepegawaian)
Master data status kepegawaian (Dosen Tetap, Dosen LB, Tenaga Kependidikan, dll)

---

### 12. `salary_ranges` (Range Gaji)
Master data rentang gaji

---

### 13. `regions` (Wilayah Koordinator)
Pembagian wilayah koordinator SPK

Relasi:
- `coordinator_user_id` → `users.id`

Pivot table: `region_provinces` (many-to-many dengan provinces)

---

### 14. `payments` (Pembayaran)
Tracking pembayaran iuran dan donasi

Foreign Keys:
- `user_id` → `users.id`
- `verified_by` → `users.id`

Status: pending, verified, rejected, cancelled

---

### 15. Forum System
Tabel: `forum_categories`, `forum_threads`, `forum_posts`, `forum_likes`, `forum_reports`

---

### 16. Survey System
Tabel: `surveys`, `survey_questions`, `survey_question_options`, `survey_responses`, `survey_response_details`

---

### 17. Complaint/Ticketing
Tabel: `complaint_categories`, `complaints`, `complaint_responses`, `complaint_attachments`, `complaint_history`

---

### 18. CMS (Content)
Tabel: `pages`, `posts`, `tags`, `post_tags`, `post_comments`

---

### 19. Navigation
Tabel: `menus` (hierarchical dengan `parent_id`)

---

### 20. WhatsApp Groups
Tabel: `wa_groups`, `wa_group_members`, `wa_group_invitations`, `wa_group_announcements`

---

### 21. Organizational Structure
Tabel: `org_units`, `org_positions`, `org_assignments`, `org_committees`, `org_committee_members`

---

### 22. Audit & Logging
Tabel: `audit_logs`, `login_logs`, `email_logs`, `system_logs`, `file_uploads`, `notification_logs`

---

### 23. RBAC (Custom Management)
Tabel: `auth_groups`, `auth_permissions`, `auth_groups_permissions`

**PENTING**:
- Ini BUKAN tabel Shield, tapi custom untuk UI management
- Shield punya tabel `auth_groups_users` (user-group relation)
- Tabel `auth_groups` ini untuk define groups dan permissions secara custom

---

## ⚠️ KESALAHAN UMUM YANG HARUS DIHINDARI

### ❌ SALAH #1: Mengakses email dari tabel users
```php
// SALAH!
$user = $userModel->find($userId);
echo $user->email; // ❌ KOLOM INI TIDAK ADA!

// BENAR!
$user = $userModel->select('users.*, auth_identities.secret as email')
    ->join('auth_identities', 'auth_identities.user_id = users.id')
    ->where('auth_identities.type', 'email_password')
    ->find($userId);
echo $user->email; // ✅
```

### ❌ SALAH #2: Search email di tabel users
```php
// SALAH!
$users = $userModel->like('email', $keyword)->findAll(); // ❌

// BENAR!
$users = $userModel->select('users.*, auth_identities.secret as email')
    ->join('auth_identities', 'auth_identities.user_id = users.id')
    ->where('auth_identities.type', 'email_password')
    ->like('auth_identities.secret', $keyword)
    ->findAll(); // ✅
```

### ❌ SALAH #3: Update email di tabel users
```php
// SALAH!
$userModel->update($userId, ['email' => $newEmail]); // ❌

// BENAR!
$db->table('auth_identities')
    ->where('user_id', $userId)
    ->where('type', 'email_password')
    ->update(['secret' => $newEmail]); // ✅
```

### ❌ SALAH #4: Menggunakan district_id / village_id di member_profiles
```php
// POTENSI ERROR! (kolom tidak ada di database)
$memberProfileModel->save([
    'user_id' => 1,
    'district_id' => 123, // ❌ KOLOM TIDAK ADA!
    'village_id' => 456,  // ❌ KOLOM TIDAK ADA!
]);

// Yang ada hanya:
// - province_id ✅
// - regency_id ✅
```

### ❌ SALAH #5: Asumsi role adalah integer ID
```php
// SALAH!
$role = $db->table('auth_groups_users')
    ->where('user_id', 1)
    ->get()->getRow()->group;
// $role = "Super Admin" (string, bukan ID!)

if ($role == 1) { // ❌ SALAH! Role adalah string!
    // ...
}

// BENAR!
if ($role == 'Super Admin') { // ✅
    // ...
}
```

---

## 🔍 CARA CEPAT CEK STRUKTUR TABEL

### Melihat Kolom Tabel
```bash
# Lihat struktur tabel users
php spark db:table users

# Atau via MySQL
mysql -u root -p spk_db -e "DESCRIBE users;"
```

### Melihat Foreign Keys
```bash
mysql -u root -p spk_db -e "SHOW CREATE TABLE member_profiles\G"
```

### Melihat Indexes
```bash
mysql -u root -p spk_db -e "SHOW INDEX FROM users;"
```

---

## 📚 REFERENSI FILE

### Migration Files
- `app/Database/Migrations/2024-01-01-000002_CreateMemberProfilesTable.php`
- `app/Database/Migrations/2025_01_15_000001_add_activation_fields_to_users.php`

### Model Files
- `app/Models/UserModel.php` - **BACA INI untuk contoh join auth_identities!**
- `app/Models/MemberProfileModel.php`

### Config Files
- `app/Config/Auth.php` - Config Shield (line 414-422 untuk nama tabel)

### Shield Documentation
- https://shield.codeigniter.com/
- Tabel Shield: users, auth_identities, auth_logins, auth_groups_users, dll.

---

## ✅ CHECKLIST SEBELUM CODING

Sebelum melakukan perubahan apapun, pastikan:

- [ ] Saya sudah membaca file ini
- [ ] Saya memahami email disimpan di `auth_identities`, bukan `users`
- [ ] Saya memahami password disimpan di `auth_identities.secret2`
- [ ] Saya memahami role disimpan sebagai string di `auth_groups_users.group`
- [ ] Saya sudah cek kolom tabel via migration atau DESCRIBE table
- [ ] Saya sudah cek contoh di UserModel.php untuk join auth_identities
- [ ] Saya tidak menggunakan kolom `district_id`/`village_id` di member_profiles
- [ ] Saya memahami foreign key constraints yang ada

---

**Dibuat**: 2025-11-21
**Last Update**: -
**Untuk**: Claude AI dan Development Team
