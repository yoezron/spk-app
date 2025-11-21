# ANALISIS BUGS DAN PERBAIKAN YANG DIPERLUKAN

> **Tanggal Analisis**: 2025-11-21
> **Berdasarkan**: DATABASE_SCHEMA_REFERENCE.md dan analisis kode lengkap

---

## 🚨 CRITICAL BUGS (HARUS SEGERA DIPERBAIKI)

### ❌ BUG #1: Role Name `'koordinator_wilayah'` TIDAK ADA DI DATABASE!

**Tingkat Keparahan**: **CRITICAL** - Menyebabkan SEMUA fitur koordinator tidak berfungsi!

**Lokasi**: 54+ file terpengaruh

**Masalah**:
- Kode menggunakan role name: `'koordinator_wilayah'`
- Tapi di database `auth_groups_users.group` yang benar adalah: `'koordinator'`
- Semua pengecekan role koordinator akan SELALU GAGAL!

**File Yang Terpengaruh**:
1. **app/Filters/RoleFilter.php:150** - `in_array('koordinator_wilayah', $userRoles)`
2. **app/Filters/RegionScopeFilter.php:119, 125** - `$user->inGroup('koordinator_wilayah')`
3. **app/Controllers/Admin/MemberController.php** - 18 lokasi menggunakan `'koordinator_wilayah'`
4. **app/Controllers/Admin/ComplaintController.php** - 11 lokasi
5. **app/Controllers/Admin/StatisticsController.php** - 5 lokasi
6. **app/Controllers/Admin/DashboardController.php** - 4 lokasi
7. **app/Controllers/Admin/WAGroupController.php** - 10 lokasi
8. **app/Views/super/users/index.php:206**
9. **app/Views/super/users/show.php:75**

**Contoh Kode Yang SALAH**:
```php
// ❌ SALAH - Role ini TIDAK ADA di database!
if ($user->inGroup('koordinator_wilayah')) {
    // Ini TIDAK AKAN PERNAH DIEKSEKUSI!
}

// ❌ SALAH - Check role yang tidak ada
$isKoordinator = $user->inGroup('koordinator_wilayah');
```

**Perbaikan Yang Diperlukan**:
```php
// ✅ BENAR - Sesuai database
if ($user->inGroup('koordinator')) {
    // Ini akan berjalan dengan benar
}

// ✅ BENAR
$isKoordinator = $user->inGroup('koordinator');
```

**Cara Memperbaiki**:
1. Search & Replace di SEMUA file: `'koordinator_wilayah'` → `'koordinator'`
2. Search & Replace: `"koordinator_wilayah"` → `"koordinator"`
3. Pastikan tidak ada yang terlewat dengan grep:
   ```bash
   grep -r "koordinator_wilayah" app/
   ```

**Dampak Jika Tidak Diperbaiki**:
- ❌ Koordinator tidak bisa login ke admin panel
- ❌ Semua fitur regional scope tidak berfungsi
- ❌ Koordinator dianggap tidak punya role apapun
- ❌ Access control untuk koordinator GAGAL TOTAL

---

### ❌ BUG #2: Akses Email Dari `users.email` (KOLOM TIDAK ADA!)

**Tingkat Keparahan**: **CRITICAL** - Menyebabkan SQL Error!

**Lokasi**: 40+ query di berbagai Controller

**Masalah**:
- Kode mencoba SELECT dan SEARCH kolom `users.email`
- Tapi tabel `users` **TIDAK PUNYA** kolom `email`!
- Email disimpan di `auth_identities.secret` (bukan di `users`)
- Query akan GAGAL dengan error: **"Unknown column 'users.email'"**

**File Yang Terpengaruh**:
1. **app/Controllers/Admin/MemberController.php**:
   - Line 196: `->select('member_profiles.*, users.email, ...')`
   - Line 215: `->orLike('users.email', $search)`
   - Line 259: `->select('member_profiles.*, users.email, ...')`
   - Line 304, 406, 876, 893, 906, 908, 1010, 1042, 1067

2. **app/Controllers/Admin/BulkImportController.php**:
   - Line 500: `->select('import_logs.*, users.email')`
   - Line 508: `->orLike('users.email', $search)`

3. **app/Controllers/Admin/ContentController.php**:
   - Line 92: `->select('posts.*, ..., users.email as author_email, ...')`
   - Line 495: `->select('pages.*, users.email as author_email, ...')`

4. **app/Controllers/Admin/PaymentController.php**:
   - Line 210: `->select('payments.*, users.username, users.email, ...')`

5. **app/Controllers/Admin/ComplaintController.php**:
   - Line 155: `->select('users.id, users.email, member_profiles.full_name')`
   - Line 219, 228, 302

6. **app/Controllers/Admin/WAGroupController.php**:
   - Line 487, 496: `->select('..., users.email')`

7. **app/Controllers/Admin/StatisticsController.php**:
   - Line 722, 741

8. **app/Controllers/Admin/SurveyController.php**:
   - Line 86

**Contoh Kode Yang SALAH**:
```php
// ❌ SALAH - users.email TIDAK ADA!
$member = $this->memberModel
    ->select('member_profiles.*, users.email, users.active')
    ->join('users', 'users.id = member_profiles.user_id')
    ->find($id);

// SQL Error: Unknown column 'users.email' in 'field list'
```

**Perbaikan Yang Diperlukan**:
```php
// ✅ BENAR - Join dengan auth_identities untuk mendapatkan email
$member = $this->memberModel
    ->select('member_profiles.*, auth_identities.secret as email, users.active')
    ->join('users', 'users.id = member_profiles.user_id')
    ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
    ->find($id);
```

**Untuk Pencarian Email**:
```php
// ❌ SALAH
->orLike('users.email', $search)

// ✅ BENAR
->orLike('auth_identities.secret', $search)
```

**Lihat Contoh di**: `app/Models/UserModel.php:182-188` (method `findByEmail()`)

**Cara Memperbaiki Sistematis**:
1. Di setiap query yang melibatkan email, tambahkan join:
   ```php
   ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
   ```
2. Ganti semua `users.email` dengan `auth_identities.secret as email`
3. Ganti semua pencarian `->orLike('users.email', ...)` dengan `->orLike('auth_identities.secret', ...)`

**Dampak Jika Tidak Diperbaiki**:
- ❌ SQL Error di semua halaman yang menampilkan email member
- ❌ Fitur pencarian member berdasarkan email tidak berfungsi
- ❌ Export data member akan gagal (kolom email kosong/error)
- ❌ Halaman daftar member, pending member, dll akan ERROR

---

### ❌ BUG #3: Penggunaan `district_id` dan `village_id` (KOLOM TIDAK ADA!)

**Tingkat Keparahan**: **HIGH** - Menyebabkan error saat insert/update member profile

**Lokasi**: 7 file

**Masalah**:
- Kode mencoba menyimpan data ke kolom `district_id` dan `village_id`
- Tapi kolom ini **TIDAK ADA** di tabel `member_profiles`!
- Hanya ada: `province_id` dan `regency_id`
- Insert/Update akan GAGAL dengan error: **"Unknown column 'district_id'"**

**File Yang Terpengaruh**:
1. **app/Models/MemberProfileModel.php:39-40**
   ```php
   'district_id',    // ❌ Kolom tidak ada!
   'village_id',     // ❌ Kolom tidak ada!
   ```

2. **app/Services/Member/RegisterMemberService.php:301-302**
   ```php
   'district_id'  => $data['district_id'] ?? null,   // ❌ ERROR!
   'village_id'   => $data['village_id'] ?? null,    // ❌ ERROR!
   ```

3. **app/Services/Member/BulkImportService.php:442-443**
   ```php
   'district_id'  => null,  // ❌ ERROR!
   'village_id'   => null,  // ❌ ERROR!
   ```

4. **app/Models/VillageModel.php** - Model untuk tabel yang mungkin tidak ada
5. **app/Models/DistrictModel.php** - Model untuk tabel yang mungkin tidak ada
6. **app/Entities/Member.php** - Entity properties yang salah
7. **app/Controllers/Api/MasterDataController.php** - API endpoint yang mungkin error

**Contoh Error**:
```php
// ❌ SALAH - Akan menyebabkan SQL error
$memberModel->save([
    'user_id' => 1,
    'full_name' => 'John Doe',
    'district_id' => 123,  // ❌ Unknown column 'district_id'
    'village_id' => 456,   // ❌ Unknown column 'village_id'
]);
```

**Perbaikan Yang Diperlukan**:

**Option 1: Hapus Kolom Dari Kode (RECOMMENDED)**
```php
// Hapus dari MemberProfileModel.php $allowedFields
// Hapus dari semua Service yang menggunakan district_id/village_id
// Hanya gunakan province_id dan regency_id
```

**Option 2: Tambah Kolom Ke Database (Jika Memang Diperlukan)**
```sql
ALTER TABLE member_profiles
ADD COLUMN district_id INT UNSIGNED NULL AFTER regency_id,
ADD COLUMN village_id INT UNSIGNED NULL AFTER district_id;

-- Tambah foreign keys jika tabel districts dan villages ada
ALTER TABLE member_profiles
ADD FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
ADD FOREIGN KEY (village_id) REFERENCES villages(id) ON DELETE SET NULL;
```

**Rekomendasi**: Gunakan **Option 1** karena:
- Tabel `districts` dan `villages` tidak ada di migration
- Database saat ini hanya menggunakan province dan regency
- Menambah district/village akan kompleks (data besar, relasi, migration)

**Dampak Jika Tidak Diperbaiki**:
- ❌ Registrasi member baru GAGAL (jika form mengirim district_id/village_id)
- ❌ Bulk import member GAGAL
- ❌ Update profil member ERROR
- ❌ SQL error di production

---

### ❌ BUG #4: Role Assignment Menggunakan Title Case

**Tingkat Keparahan**: **HIGH** - Role assignment gagal

**Lokasi**: 2 file Service

**Masalah**:
- Kode mengassign role menggunakan Title Case dengan spasi: `'Calon Anggota'`, `'Anggota'`
- Tapi role yang valid di database adalah lowercase dengan underscore: `'calon_anggota'`, `'anggota'`
- Shield akan mencari role dengan nama persis, jika tidak ada → assignment GAGAL

**File Yang Terpengaruh**:
1. **app/Services/Member/RegisterMemberService.php:83**
   ```php
   // ❌ SALAH - Role 'Calon Anggota' tidak ada!
   $user->addGroup('Calon Anggota');
   ```

2. **app/Services/Member/BulkImportService.php:259**
   ```php
   // ❌ SALAH - Default role Title Case
   $role = $options['default_role'] ?? 'Anggota';
   $user->addGroup($role);  // Akan gagal jika $role = 'Anggota'
   ```

**Perbaikan Yang Diperlukan**:
```php
// ✅ BENAR
$user->addGroup('calon_anggota');  // RegisterMemberService.php:83

// ✅ BENAR
$role = $options['default_role'] ?? 'anggota';  // BulkImportService.php:259
```

**Dampak Jika Tidak Diperbaiki**:
- ❌ User baru tidak punya role sama sekali
- ❌ User tidak bisa akses fitur apapun (semua role checks gagal)
- ❌ Import member berhasil tapi tanpa role

---

## ⚠️ MEDIUM PRIORITY BUGS

### ⚠️ BUG #5: RolesSeeder Menyimpan Role Ke Tabel Yang Salah

**Tingkat Keparahan**: **MEDIUM** - Tidak akan langsung error, tapi role management jadi bingung

**Lokasi**: `app/Database/Seeds/RolesSeeder.php`

**Masalah**:
- Seeder menyimpan role ke tabel `auth_groups` (custom RBAC table untuk UI management)
- Menggunakan field `title` dengan nilai Title Case: "Super Admin", "Koordinator Wilayah", dll
- Tapi Shield menggunakan tabel `auth_groups_users.group` dengan nilai lowercase

**Kode Saat Ini**:
```php
// RolesSeeder.php line 70-74
$this->db->table('auth_groups')->insert([
    'title'       => 'Super Admin',      // ❌ Title Case
    'description' => $role['description'],
    'created_at'  => $role['created_at'],
]);
```

**Masalah**:
- Tabel `auth_groups` adalah custom table untuk manage roles via UI
- Tabel `auth_groups_users` adalah Shield table untuk assign roles ke user
- Keduanya harus SYNC!

**Perbaikan**:
1. Update seeder untuk menyimpan role dengan key lowercase:
   ```php
   $roles = [
       'superadmin' => [
           'title' => 'Super Admin',
           'description' => '...',
       ],
       'koordinator' => [
           'title' => 'Koordinator',  // BUKAN 'Koordinator Wilayah'!
           'description' => '...',
       ],
       // dst
   ];
   ```

2. Atau lebih baik, buat seeder mengacu ke `Config/AuthGroups.php`

**Catatan**: Tabel `auth_groups` missing role `'koordinator'` (hanya ada 'Super Admin', 'Pengurus', 'Koordinator Wilayah', 'Anggota', 'Calon Anggota')

---

### ⚠️ BUG #6: Akses Property `->email` Pada User Object

**Tingkat Keparahan**: **MEDIUM** - Mungkin berfungsi kadang-kadang tergantung context

**Lokasi**: Berbagai Controller dan Service

**Masalah**:
- Kode mengakses `$user->email` secara langsung
- Shield User entity mungkin tidak selalu memiliki property `email` ter-load
- Bisa null atau undefined tergantung bagaimana user di-query

**Contoh**:
```php
// app/Controllers/Super/UserController.php:352
'email' => $user->email,  // ⚠️ Might be null

// app/Controllers/Auth/VerifyController.php:54
'email' => auth()->user()->email  // ⚠️ Might be null

// app/Helpers/auth_helper.php:61
return $user ? $user->email : null;  // ⚠️ Might be null
```

**Perbaikan**:
Gunakan method yang pasti:
```php
// ✅ Lebih aman
$email = $user->getEmailIdentity()?->secret;

// Atau query ulang dengan join auth_identities
$userModel = model('UserModel');
$userData = $userModel->select('users.*, auth_identities.secret as email')
    ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
    ->find($userId);
```

---

## 📝 WARNING LAINNYA

### ⚠️ WARNING #1: Missing 'koordinator' Role di AuthGroups Config

**File**: `app/Config/AuthGroups.php`

**Masalah**:
```php
public array $groups = [
    'superadmin' => [...],
    'admin' => [...],       // ⚠️ Role 'admin' ada tapi tidak digunakan
    'pengurus' => [...],
    'anggota' => [...],
    'calon_anggota' => [...],
    'user' => [...],        // ⚠️ Role 'user' ada tapi tidak digunakan
];
```

**Missing**: Role `'koordinator'` tidak ada di config!

**Perbaikan**:
```php
'koordinator' => [
    'title'       => 'Koordinator',
    'description' => 'Regional coordinators.',
],
```

---

### ⚠️ WARNING #2: Inconsistent Role Display Names

**Masalah**:
- Seeder: "Koordinator Wilayah"
- Code checks: `'koordinator_wilayah'` atau `'koordinator'`
- Config: Missing
- Display function: format_user_role() returns 'Koordinator Wilayah'

**Dampak**:
- User bingung dengan nama role
- Inkonsistensi di UI

**Rekomendasi**:
Standardisasi:
- **Database key**: `'koordinator'`
- **Display name**: `'Koordinator'` atau `'Koordinator Wilayah'` (pilih satu!)
- Update semua tempat untuk konsisten

---

## 📋 CHECKLIST PERBAIKAN PRIORITAS

### CRITICAL (Harus diperbaiki SEKARANG):
- [ ] **BUG #1**: Replace semua `'koordinator_wilayah'` → `'koordinator'` (54+ file)
- [ ] **BUG #2**: Fix semua query `users.email` → join `auth_identities` (40+ query)
- [ ] **BUG #3**: Hapus `district_id`, `village_id` dari MemberProfileModel dan Services
- [ ] **BUG #4**: Fix role assignment di RegisterMemberService dan BulkImportService

### HIGH (Perbaiki segera setelah CRITICAL):
- [ ] **BUG #5**: Update RolesSeeder untuk sync dengan AuthGroups
- [ ] **WARNING #1**: Tambah role 'koordinator' ke AuthGroups.php

### MEDIUM (Bisa diperbaiki bertahap):
- [ ] **BUG #6**: Review semua akses `$user->email`, gunakan getEmailIdentity()
- [ ] **WARNING #2**: Standardisasi role display names

---

## 🔧 SCRIPT HELPER UNTUK MEMPERBAIKI

### Script 1: Find and Replace koordinator_wilayah
```bash
# Dry-run (preview changes)
find app/ -type f -name "*.php" -exec grep -l "koordinator_wilayah" {} \;

# Actual replace (BACKUP DULU!)
find app/ -type f -name "*.php" -exec sed -i "s/'koordinator_wilayah'/'koordinator'/g" {} \;
find app/ -type f -name "*.php" -exec sed -i 's/"koordinator_wilayah"/"koordinator"/g' {} \;
```

### Script 2: Find Files With users.email
```bash
grep -r "users\.email" app/ --include="*.php"
```

---

## 📚 REFERENSI

- **Database Schema**: `.claude/DATABASE_SCHEMA_REFERENCE.md`
- **UserModel Example**: `app/Models/UserModel.php:182-188` (findByEmail method)
- **AuthGroups Config**: `app/Config/AuthGroups.php`
- **Shield Auth Config**: `app/Config/Auth.php:414-422`
- **Migration**: `app/Database/Migrations/2024-01-01-000002_CreateMemberProfilesTable.php`

---

## ✅ TESTING CHECKLIST SETELAH PERBAIKAN

Setelah memperbaiki bugs di atas, TEST hal-hal berikut:

1. **Role Koordinator**:
   - [ ] Koordinator bisa login
   - [ ] Koordinator bisa akses /admin/dashboard
   - [ ] Koordinator hanya lihat data di wilayahnya
   - [ ] Filter regional scope berfungsi

2. **Email Access**:
   - [ ] Halaman daftar member menampilkan email dengan benar
   - [ ] Search member by email berfungsi
   - [ ] Export data member include email
   - [ ] Email ditampilkan di detail member

3. **Registrasi Member**:
   - [ ] Member baru bisa register
   - [ ] Role 'calon_anggota' ter-assign
   - [ ] Tidak ada error SQL saat save

4. **Bulk Import**:
   - [ ] Import Excel berhasil
   - [ ] Role ter-assign dengan benar
   - [ ] Tidak ada error district_id/village_id

---

**Catatan Akhir**:
Bugs ini SANGAT KRITIS dan menyebabkan banyak fitur tidak berfungsi. Prioritaskan perbaikan BUG #1 dan #2 terlebih dahulu karena paling banyak berdampak.
