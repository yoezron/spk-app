# 🔧 Fix: Member Stuck di Pending Status

## ❌ Masalah

Member yang sudah di-approve masih muncul di halaman **Calon Anggota** (`/admin/members?status=pending`), padahal seharusnya sudah pindah ke **Daftar Anggota** (`/admin/members?status=active`).

**Contoh kasus:**
- **Isman Rahmani Yusron** (SPK-2025-00001) → Sudah punya nomor anggota tapi status masih `pending`
- **Pengurus Serikat** → Status `pending` dan `member_number = NULL`

---

## 🔍 Root Cause Analysis

### 1. **Missing Database Columns**

File `app/Services/Member/ApproveMemberService.php` mencoba update kolom yang tidak ada di database:

```php
// ApproveMemberService.php line 96-97
$memberUpdateData = [
    'membership_status' => 'active',
    'verified_at' => date('Y-m-d H:i:s'),  // ❌ Kolom tidak ada!
    'verified_by' => $approvedBy,          // ❌ Kolom tidak ada!
];
```

**Akibatnya:**
- Query UPDATE gagal (silent failure karena tidak ada error handling)
- Status member tetap `pending` meskipun role sudah diganti ke `anggota`
- Member number sudah ter-generate tapi status tidak ter-update

### 2. **Role Name Case Mismatch** ✅ SUDAH DI-FIX

Masalah ini sudah diperbaiki di commit sebelumnya:
- ❌ **Sebelumnya:** `ApproveMemberService` pakai `'Calon Anggota'`, `'Anggota'` (Title Case)
- ✅ **Sekarang:** Sudah diubah ke `'calon_anggota'`, `'anggota'` (lowercase)

---

## 📋 Langkah Perbaikan

### Step 1: Tambah Kolom yang Hilang

1. Buka **phpMyAdmin**
2. Pilih database **`spk_db`**
3. Klik tab **SQL**
4. Jalankan query berikut:

```sql
-- Tambah kolom verified_at
ALTER TABLE member_profiles
ADD COLUMN verified_at DATETIME NULL
COMMENT 'Timestamp when member was verified/approved'
AFTER join_date;

-- Tambah kolom verified_by
ALTER TABLE member_profiles
ADD COLUMN verified_by INT(11) UNSIGNED NULL
COMMENT 'User ID of admin who verified this member'
AFTER verified_at;

-- Tambah foreign key
ALTER TABLE member_profiles
ADD CONSTRAINT fk_member_profiles_verified_by
FOREIGN KEY (verified_by) REFERENCES users(id)
ON DELETE SET NULL ON UPDATE CASCADE;
```

**Expected result:**
```
Query OK, X rows affected
```

---

### Step 2: Fix Member yang Stuck

**CARA A: Menggunakan SQL Script Lengkap**

1. Buka file: **`/database/sql/fix_stuck_pending_members.sql`**
2. Copy **SELURUH ISI** file (sudah di-update dengan perbaikan)
3. Paste di phpMyAdmin SQL tab
4. Klik **Go**

Script ini akan:
- ✅ Cek apakah kolom sudah ada (query #0)
- ✅ Update member yang sudah punya `member_number` tapi `status = pending` → jadi `active`
- ✅ Update role dari `calon_anggota` → `anggota` (jika perlu)
- ✅ Aktifkan user account
- ✅ Verifikasi hasil

**CARA B: Manual Query (Untuk Quick Fix)**

Jika hanya ingin cepat fix tanpa diagnostic:

```sql
-- Fix member yang sudah punya nomor anggota tapi masih pending
UPDATE member_profiles
SET
    membership_status = 'active',
    verified_at = NOW(),
    join_date = COALESCE(join_date, NOW())
WHERE membership_status = 'pending'
AND member_number IS NOT NULL;

-- Pastikan user account active
UPDATE users u
JOIN member_profiles mp ON mp.user_id = u.id
SET u.active = 1
WHERE mp.membership_status = 'active'
AND u.active = 0;

-- Update role dari calon_anggota ke anggota
UPDATE auth_groups_users
SET `group` = 'anggota'
WHERE user_id IN (
    SELECT user_id
    FROM member_profiles
    WHERE membership_status = 'active'
)
AND `group` = 'calon_anggota';
```

---

### Step 3: Verifikasi Fix Berhasil

Jalankan query verifikasi:

```sql
-- Cek berapa member masih stuck
SELECT
    COUNT(*) as total_pending,
    SUM(CASE WHEN member_number IS NOT NULL THEN 1 ELSE 0 END) as stuck_with_number,
    SUM(CASE WHEN member_number IS NULL THEN 1 ELSE 0 END) as genuine_pending
FROM member_profiles
WHERE membership_status = 'pending';
```

**Expected result setelah fix:**
```
total_pending: 1-2 (hanya calon anggota yang belum diapprove)
stuck_with_number: 0 (tidak ada yang stuck)
genuine_pending: 1-2 (sama dengan total_pending)
```

---

## 🐛 Troubleshooting

### Masalah: Member masih stuck setelah jalankan fix

**Cek detail member yang bermasalah:**

```sql
SELECT
    mp.id,
    mp.full_name,
    mp.member_number,
    mp.membership_status,
    agu.group as current_role,
    u.active as user_active,
    mp.verified_at,
    mp.join_date
FROM member_profiles mp
JOIN users u ON u.id = mp.user_id
LEFT JOIN auth_groups_users agu ON agu.user_id = mp.user_id
WHERE mp.membership_status = 'pending'
ORDER BY mp.id;
```

**Diagnosis berdasarkan hasil:**

| Kondisi | Diagnosis | Solusi |
|---------|-----------|--------|
| `member_number != NULL` dan `status = pending` | Member stuck | Jalankan fix query di Step 2 |
| `group = 'anggota'` dan `member_number = NULL` | Anomali: Role sudah anggota tapi no nomor | Generate nomor anggota manual (lihat Step 4) |
| `group = 'calon_anggota'` dan `member_number = NULL` | Normal: Belum di-approve | Tidak perlu fix |

---

### Step 4: Fix Anomali - Member dengan Role Anggota tapi Belum Punya Nomor

Jika ada member dengan `group = 'anggota'` tapi `member_number = NULL`:

```sql
-- Dapatkan nomor terakhir
SELECT member_number
FROM member_profiles
WHERE member_number IS NOT NULL
ORDER BY member_number DESC
LIMIT 1;
-- Misal hasil: SPK-2025-00001

-- Generate nomor berikutnya dan update member
UPDATE member_profiles mp
JOIN auth_groups_users agu ON agu.user_id = mp.user_id
SET
    mp.member_number = 'SPK-2025-00002',  -- Nomor urut berikutnya
    mp.membership_status = 'active',
    mp.verified_at = NOW(),
    mp.verified_by = 1,  -- Ganti dengan user_id admin
    mp.join_date = COALESCE(mp.join_date, NOW())
WHERE mp.id = XXX  -- Ganti dengan ID member
AND agu.group = 'anggota'
AND mp.member_number IS NULL;
```

---

## ✅ Expected Result

Setelah semua fix dijalankan:

### Di Halaman Admin

**1. `/admin/members?status=pending` (Calon Anggota):**
- ✅ Hanya menampilkan member yang **benar-benar belum di-approve**
- ✅ Tidak ada member yang sudah punya `member_number`
- ✅ Semua member di sini punya `group = 'calon_anggota'`

**2. `/admin/members?status=active` (Daftar Anggota):**
- ✅ Menampilkan **Isman Rahmani Yusron** (SPK-2025-00001)
- ✅ Menampilkan member lain yang sudah di-approve
- ✅ Semua punya `member_number` dan `group = 'anggota'`

### Di Database

```sql
-- Semua member dengan member_number sudah active
SELECT * FROM member_profiles WHERE member_number IS NOT NULL;
-- Expected: membership_status = 'active'

-- Tidak ada member stuck
SELECT COUNT(*) FROM member_profiles
WHERE membership_status = 'pending' AND member_number IS NOT NULL;
-- Expected: 0
```

---

## 🔄 Mencegah Masalah di Masa Depan

### 1. Jalankan Migration

Agar kolom `verified_at` dan `verified_by` tetap ada di production:

```bash
# Di server production, jalankan:
php spark migrate
```

File migration sudah dibuat:
- `app/Database/Migrations/2025_12_01_000001_add_verification_fields_to_member_profiles.php`

### 2. Monitoring

Jalankan query monitoring secara berkala:

```sql
-- Cek member yang mungkin stuck
SELECT
    mp.full_name,
    mp.member_number,
    mp.membership_status,
    agu.group,
    DATEDIFF(NOW(), mp.created_at) as days_pending
FROM member_profiles mp
LEFT JOIN auth_groups_users agu ON agu.user_id = mp.user_id
WHERE (
    -- Member punya nomor tapi status pending (STUCK!)
    (mp.member_number IS NOT NULL AND mp.membership_status = 'pending')
    OR
    -- Member role anggota tapi no nomor (ANOMALI!)
    (agu.group = 'anggota' AND mp.member_number IS NULL)
    OR
    -- Member pending > 30 hari (PERLU REVIEW!)
    (mp.membership_status = 'pending' AND DATEDIFF(NOW(), mp.created_at) > 30)
);
```

---

## 📞 Support

Jika setelah fix masalah masih ada:

1. **Screenshot** halaman `/admin/members?status=pending`
2. **Copy hasil** query diagnostic:
   ```sql
   SELECT mp.*, agu.group, u.active
   FROM member_profiles mp
   JOIN users u ON u.id = mp.user_id
   LEFT JOIN auth_groups_users agu ON agu.user_id = mp.user_id
   WHERE mp.membership_status = 'pending';
   ```
3. **Share** informasi: Nama member, expected behavior, actual behavior

---

## ✅ Checklist

Setelah semua fix dijalankan:

- [ ] Kolom `verified_at` dan `verified_by` sudah ada di tabel `member_profiles`
- [ ] Query "stuck_with_number" return 0
- [ ] Member dengan nomor anggota sudah pindah ke "Daftar Anggota"
- [ ] Calon Anggota hanya berisi member yang belum di-approve
- [ ] Tidak ada error di log approval (cek `writable/logs/`)
- [ ] Test approval member baru → berhasil pindah ke Daftar Anggota

---

**Last Updated:** 2025-12-01
**Version:** 2.0.0
**Status:** Production Ready ✅
**Related Files:**
- `/database/sql/fix_stuck_pending_members.sql`
- `/app/Services/Member/ApproveMemberService.php`
- `/app/Database/Migrations/2025_12_01_000001_add_verification_fields_to_member_profiles.php`
