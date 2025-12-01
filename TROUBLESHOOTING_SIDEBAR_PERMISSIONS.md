# Troubleshooting: Menu Sidebar Tidak Muncul

## Masalah
Menu sidebar tidak menampilkan item menu meskipun permission sudah diceklis oleh superadmin untuk role "Pengurus".

## Penyebab & Solusi

### ✅ SOLUSI 1: Populate Data Menu ke Database

**Penyebab:** Tabel `menus` masih kosong, belum ada data menu.

**Cara Cek:**
```sql
-- Jalankan query ini di phpMyAdmin atau MySQL client
SELECT COUNT(*) as total_menus FROM menus;
```

Jika hasilnya `0`, berarti tabel kosong dan perlu di-populate.

**Cara Fix:**
1. Buka **phpMyAdmin** atau MySQL client favorit
2. Pilih database aplikasi (biasanya `spk_db`)
3. Buka file `/database/sql/seed_menus.sql`
4. Copy seluruh isi file tersebut
5. Paste dan jalankan di phpMyAdmin
6. Refresh halaman admin dashboard
7. Menu seharusnya sudah muncul ✅

**Atau via CLI (jika composer installed):**
```bash
cd /home/user/spk-app
php spark db:seed MenuSeeder
```

---

### ✅ SOLUSI 2: Assign Permission ke Role Pengurus

**Penyebab:** Permission belum di-assign ke role "Pengurus" oleh superadmin.

**Cara Cek:**
1. Buka file `/database/sql/check_pengurus_permissions.sql`
2. Copy dan jalankan di phpMyAdmin
3. Lihat hasil query ke-3: "CEK PERMISSIONS YANG SUDAH DIASSIGN KE PENGURUS"

Jika hasilnya kosong atau hanya sedikit, berarti permission belum di-assign.

**Cara Fix - Via Superadmin Dashboard (RECOMMENDED):**
1. Login sebagai **superadmin**
2. Buka menu **Super Admin → Role & Permission → Daftar Role**
3. Klik tombol **Edit** pada role **"Pengurus"**
4. Scroll ke bagian **Permissions**
5. **Centang/Check** semua permission yang ingin diberikan:

   **Untuk Full Access Pengurus, centang:**
   - ☑️ `admin.dashboard` - Dashboard Admin
   - ☑️ `member.view` - Melihat daftar anggota
   - ☑️ `member.edit` - Edit anggota
   - ☑️ `member.approve` - **Approve calon anggota** ← ini yang dimaksud user
   - ☑️ `member.import` - Import bulk anggota
   - ☑️ `member.export` - Export data anggota
   - ☑️ `payment.view` - Melihat pembayaran
   - ☑️ `payment.verify` - Verifikasi pembayaran
   - ☑️ `payment.report` - Laporan keuangan
   - ☑️ `statistics.view` - Statistik & laporan
   - ☑️ `statistics.export` - Export statistik
   - ☑️ `survey.manage` - Kelola survei
   - ☑️ `survey.create` - Buat survei
   - ☑️ `survey.view_results` - Lihat hasil survei
   - ☑️ `complaint.view` - Lihat pengaduan
   - ☑️ `complaint.manage` - Kelola pengaduan
   - ☑️ `forum.moderate` - Moderasi forum
   - ☑️ `content.manage` - Kelola konten
   - ☑️ `content.create` - Buat konten
   - ☑️ `org_structure.view` - Lihat struktur organisasi
   - ☑️ `org_structure.manage` - Kelola struktur
   - ☑️ `org_structure.assign` - Assign jabatan
   - ☑️ `wa_group.manage` - Kelola WhatsApp groups

6. Klik tombol **Save** atau **Update**
7. **Logout** dan **Login ulang** sebagai pengurus
8. Menu seharusnya sudah muncul ✅

**Cara Fix - Via SQL (Manual):**
```sql
-- 1. Dapatkan ID role Pengurus
SET @pengurus_id = (SELECT id FROM auth_groups WHERE title = 'Pengurus' LIMIT 1);

-- 2. Assign semua permission yang dibutuhkan
INSERT INTO auth_groups_permissions (group_id, permission_id, created_at)
SELECT @pengurus_id, ap.id, NOW()
FROM auth_permissions ap
WHERE ap.name IN (
    'admin.dashboard',
    'member.view',
    'member.edit',
    'member.approve',    -- PENTING: Ini untuk approve anggota
    'member.import',
    'member.export',
    'payment.view',
    'payment.verify',
    'payment.report',
    'statistics.view',
    'statistics.export',
    'survey.manage',
    'survey.create',
    'survey.view_results',
    'complaint.view',
    'complaint.manage',
    'forum.moderate',
    'content.manage',
    'content.create',
    'org_structure.view',
    'org_structure.manage',
    'org_structure.assign',
    'wa_group.manage'
)
AND NOT EXISTS (
    -- Jangan duplikasi jika sudah ada
    SELECT 1 FROM auth_groups_permissions agp
    WHERE agp.group_id = @pengurus_id
    AND agp.permission_id = ap.id
);

-- 3. Verifikasi
SELECT COUNT(*) as total_permissions_assigned
FROM auth_groups_permissions
WHERE group_id = @pengurus_id;
```

---

### ✅ SOLUSI 3: Clear Cache & Logout/Login Ulang

**Penyebab:** Session lama masih cache permission lama.

**Cara Fix:**
1. **Logout** dari aplikasi
2. Clear browser cache (Ctrl+Shift+Delete)
3. **Login ulang** sebagai user dengan role "Pengurus"
4. Menu seharusnya sudah muncul ✅

---

### ✅ SOLUSI 4: Verifikasi Permission Keys Match

**Penyebab:** Permission key di menu tidak cocok dengan permission key di database.

**Cara Cek:**
```sql
-- Jalankan query ini untuk melihat menu yang permission_key-nya tidak valid
SELECT
    m.id,
    m.title as menu_title,
    m.permission_key as menu_permission,
    'PERMISSION NOT FOUND!' as status
FROM menus m
WHERE m.permission_key IS NOT NULL
AND m.permission_key NOT IN (
    SELECT name FROM auth_permissions
)
ORDER BY m.sort_order;
```

Jika ada hasil, berarti ada menu yang permission key-nya salah/tidak ada.

**Cara Fix:**
```sql
-- Update permission_key yang salah
-- Contoh: Jika menu punya permission_key 'member.approve' tapi di auth_permissions namanya 'members.approve'

UPDATE menus
SET permission_key = 'member.approve'
WHERE permission_key = 'members.approve';  -- Ganti dengan permission_key yang salah
```

---

## Debugging Step-by-Step

### 1. Cek User Role
```sql
-- Lihat role apa saja yang dimiliki user
SELECT u.username, u.email, agu.group
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.username = 'username_pengurus';  -- Ganti dengan username yang ditest
```

### 2. Cek Permission User
```sql
-- Lihat permission apa saja yang dimiliki user melalui role-nya
SELECT DISTINCT ap.name as permission_key
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
JOIN auth_groups ag ON ag.title = agu.group
JOIN auth_groups_permissions agp ON agp.group_id = ag.id
JOIN auth_permissions ap ON ap.id = agp.permission_id
WHERE u.username = 'username_pengurus'  -- Ganti dengan username yang ditest
ORDER BY ap.name;
```

### 3. Cek Menu vs Permission
```sql
-- Lihat menu mana saja yang seharusnya muncul untuk user
SELECT
    m.id,
    m.parent_id,
    m.title,
    m.url,
    m.permission_key,
    CASE
        WHEN m.permission_key IS NULL THEN 'PUBLIC (Always Show)'
        WHEN m.permission_key IN (
            SELECT ap.name
            FROM users u
            JOIN auth_groups_users agu ON agu.user_id = u.id
            JOIN auth_groups ag ON ag.title = agu.group
            JOIN auth_groups_permissions agp ON agp.group_id = ag.id
            JOIN auth_permissions ap ON ap.id = agp.permission_id
            WHERE u.username = 'username_pengurus'  -- Ganti username
        ) THEN 'VISIBLE'
        ELSE 'HIDDEN (No Permission)'
    END as visibility_status
FROM menus m
WHERE m.is_active = 1
ORDER BY m.sort_order;
```

---

## Checklist Verifikasi

Setelah melakukan solusi di atas, verifikasi dengan checklist ini:

- [ ] Tabel `menus` sudah terisi data (minimal 20+ menu items)
- [ ] Role "Pengurus" sudah punya permissions yang diassign
- [ ] Permission `member.approve` sudah di-assign ke role "Pengurus"
- [ ] User sudah logout dan login ulang
- [ ] Browser cache sudah di-clear
- [ ] Layout admin sudah menggunakan component sidebar dinamis
- [ ] Ketika login sebagai pengurus, sidebar menampilkan menu sesuai permission

---

## Expected Result

Setelah semua solusi di atas diterapkan, ketika login sebagai **Pengurus** yang punya permission `member.approve`, menu sidebar seharusnya menampilkan:

```
Dashboard
└─ Dashboard

Kelola Anggota
├─ Daftar Anggota
├─ Calon Anggota         ← INI SEHARUSNYA MUNCUL (karena punya permission member.approve)
└─ Export Data

Import Anggota

Statistik & Laporan

Pembayaran
├─ Daftar Pembayaran
├─ Perlu Verifikasi      ← INI MUNCUL jika punya permission payment.verify
└─ Laporan Keuangan

... dan menu lainnya sesuai permission
```

---

## Jika Masih Tidak Bisa

Jika setelah mengikuti semua solusi di atas menu masih tidak muncul, lakukan hal berikut:

### 1. Enable Debug Mode
Edit file `.env`:
```
CI_ENVIRONMENT = development
```

### 2. Cek Error Log
Buka file `writable/logs/log-YYYY-MM-DD.log` (tanggal hari ini)

### 3. Test Permission Manual di Controller
Tambahkan kode debug di controller:
```php
// Di DashboardController atau controller mana saja
public function index()
{
    $user = auth()->user();

    // Debug: Tampilkan semua permission user
    log_message('debug', 'User permissions: ' . json_encode($user->getPermissions()));

    // Debug: Test specific permission
    $canApproveMember = $user->can('member.approve');
    log_message('debug', 'Can approve member: ' . ($canApproveMember ? 'YES' : 'NO'));

    // Debug: Cek menu yang di-return MenuService
    $menuService = new \App\Services\MenuService();
    $menus = $menuService->getMenuForUser($user->id);
    log_message('debug', 'Menus for user: ' . json_encode($menus));

    // ... rest of controller code
}
```

### 4. Hubungi Developer
Jika masih tidak berhasil, provide informasi berikut:
- Screenshot sidebar
- Screenshot permission assignment di superadmin dashboard
- Copy paste hasil query dari `check_pengurus_permissions.sql`
- Copy paste error log (jika ada)

---

## Quick Test

Untuk quick test apakah sistem permission sudah bekerja:

**Test 1: Login sebagai Superadmin**
- Expected: Semua menu muncul (bypass semua permission check)

**Test 2: Login sebagai Pengurus dengan Full Permission**
- Expected: Semua menu pengurus muncul

**Test 3: Login sebagai Pengurus dengan Limited Permission**
- Remove permission `member.approve` dari role Pengurus
- Expected: Menu "Calon Anggota" tidak muncul

Jika Test 3 berhasil, berarti sistem permission sudah berfungsi dengan baik! ✅

---

**Last Updated:** 2025-01-30
**Version:** 1.1.0
