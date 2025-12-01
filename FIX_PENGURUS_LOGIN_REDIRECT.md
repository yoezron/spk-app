# 🔧 Fix: Pengurus Di-redirect ke Member Dashboard

## ❌ Masalah

User dengan role **Pengurus** setelah login malah di-redirect ke:
- ❌ **`http://localhost:8080/member/dashboard`** (Dashboard Anggota)

Padahal seharusnya di-redirect ke:
- ✅ **`http://localhost:8080/admin/dashboard`** (Dashboard Pengurus)

---

## 🔍 Root Cause

**Case Sensitivity Mismatch antara Database dan Kode PHP:**

1. **Di Kode PHP** (`app/Controllers/Auth/LoginController.php:163`):
   ```php
   if ($user->inGroup('pengurus')) {  // ← cek lowercase 'pengurus'
       return redirect()->to('/admin/dashboard');
   }
   ```

2. **Di Database** (`auth_groups_users` table):
   ```sql
   -- Yang tersimpan mungkin:
   group = 'Pengurus'  -- ← Title case, BUKAN lowercase!
   ```

3. **Akibatnya**:
   - `$user->inGroup('pengurus')` return **FALSE** (karena 'pengurus' ≠ 'Pengurus')
   - Kondisi `if` TIDAK TERPENUHI
   - User jatuh ke **default redirect** → `/member/dashboard` (line 185)

---

## 📋 Langkah Diagnosis

### Step 1: Cek Apakah Ini Masalahnya

**Jalankan query diagnostic:**

1. Buka **phpMyAdmin**
2. Pilih database **`spk_db`**
3. Klik tab **SQL**
4. Buka file: **`/database/sql/diagnose_login_redirect.sql`**
5. Copy dan jalankan **Query #3 dan #4**

**Query #3** (Cek lowercase 'pengurus'):
```sql
SELECT COUNT(*) as total_pengurus_lowercase
FROM auth_groups_users
WHERE `group` = 'pengurus';  -- lowercase
```

**Query #4** (Cek Title case 'Pengurus'):
```sql
SELECT COUNT(*) as total_pengurus_titlecase
FROM auth_groups_users
WHERE `group` = 'Pengurus';  -- Title case
```

**Hasil yang MENUNJUKKAN MASALAH:**
- Query #3 return: **0** (tidak ada user dengan group lowercase)
- Query #4 return: **> 0** (ada user dengan group Title case)

Jika hasil seperti ini, artinya **CONFIRMED** ada case mismatch!

---

## ✅ Solusi: Update Group Name ke Lowercase

### Step 2: Jalankan Fix SQL

1. **BACKUP DATABASE DULU!** (PENTING!)
   ```bash
   # Via terminal
   mysqldump -u root -p spk_db > backup_before_fix.sql
   ```

2. Buka **phpMyAdmin**
3. Pilih database **`spk_db`**
4. Klik tab **SQL**
5. Buka file: **`/database/sql/fix_login_redirect_group_case.sql`**
6. Copy **SELURUH ISI** file tersebut
7. Paste dan **Jalankan**

**Script ini akan:**
- Update `'Pengurus'` → `'pengurus'`
- Update `'Anggota'` → `'anggota'`
- Update `'Calon Anggota'` → `'calon_anggota'`
- Update `'Super Admin'` → `'superadmin'`
- Update `'Koordinator'` → `'koordinator'`

---

### Step 3: Logout & Login Ulang

**PENTING:** Session masih cache group name lama!

1. **Logout** dari aplikasi
2. **Clear browser cache** (Ctrl+Shift+Delete)
3. **Close semua tab** browser
4. **Login ulang** sebagai user dengan role Pengurus

---

## ✅ Expected Result

Setelah login sebagai **Pengurus**:

1. ✅ **URL berubah ke:** `http://localhost:8080/admin/dashboard`
2. ✅ **Sidebar menampilkan menu admin** sesuai permission:
   ```
   Dashboard
   Kelola Anggota
   ├─ Daftar Anggota
   ├─ Calon Anggota
   └─ Export Data
   Import Anggota
   Pembayaran
   ├─ Daftar Pembayaran
   ├─ Perlu Verifikasi
   └─ Laporan Keuangan
   Statistik & Laporan
   Struktur Organisasi
   ... dll
   ```
3. ✅ **Menu member TIDAK muncul** (hanya menu admin)

---

## 🐛 Troubleshooting

### Masalah: Sudah jalankan fix, tapi masih redirect ke /member/dashboard

**Kemungkinan penyebab:**

#### 1. Session masih cache group lama
**Solusi:**
```bash
# Delete session files
rm -rf writable/session/*
```
Atau logout, clear cache, restart browser.

#### 2. Group belum ter-update dengan benar
**Cek:**
```sql
SELECT u.username, agu.group
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.username = 'USERNAME_PENGURUS';  -- Ganti dengan username yang login
```

Jika hasilnya masih `'Pengurus'` (Title case), berarti update gagal.

**Fix manual:**
```sql
UPDATE auth_groups_users
SET `group` = 'pengurus'
WHERE `group` = 'Pengurus';
```

#### 3. User punya multiple groups
**Cek:**
```sql
SELECT *
FROM auth_groups_users
WHERE user_id = 123;  -- Ganti dengan ID user
```

Jika user punya 2+ groups, Shield mengecek sesuai priority order:
1. superadmin (tertinggi)
2. pengurus
3. koordinator
4. anggota
5. calon_anggota

User akan di-redirect sesuai group tertinggi.

**Jika user punya both 'pengurus' dan 'anggota':**
Seharusnya di-redirect ke `/admin/dashboard` (pengurus lebih tinggi).

#### 4. Routes atau Filter bermasalah
**Cek:**
```bash
cd /home/user/spk-app
php spark routes | grep dashboard
```

Expected output:
```
GET     /admin/dashboard    Admin\DashboardController::index
GET     /member/dashboard   Member\DashboardController::index
GET     /super/dashboard    Super\DashboardController::index
```

Jika tidak ada `/admin/dashboard`, berarti route belum terdaftar.

---

## 🔍 Verifikasi Fix Berhasil

Jalankan query verifikasi:

```sql
-- 1. Cek semua group sudah lowercase
SELECT DISTINCT `group` FROM auth_groups_users;
-- Expected: 'superadmin', 'pengurus', 'anggota', 'calon_anggota', 'koordinator'

-- 2. Simulate redirect logic
SELECT
    u.username,
    agu.group,
    CASE
        WHEN agu.group = 'superadmin' THEN '/super/dashboard'
        WHEN agu.group = 'pengurus' THEN '/admin/dashboard'  -- ✅ HARUS INI
        WHEN agu.group = 'koordinator' THEN '/admin/dashboard'
        WHEN agu.group = 'anggota' THEN '/member/dashboard'
        WHEN agu.group = 'calon_anggota' THEN '/member/dashboard'
        ELSE '/member/dashboard (DEFAULT)'
    END as redirect_target
FROM users u
JOIN auth_groups_users agu ON agu.user_id = u.id
WHERE u.active = 1
ORDER BY agu.group;
```

---

## 📚 Penjelasan Teknis

### Mengapa Shield Case-Sensitive?

CodeIgniter Shield menggunakan **exact string comparison** untuk group checking:

```php
// Di vendor/codeigniter4/shield/src/Models/UserModel.php
public function inGroup(string $group): bool
{
    return in_array($group, $this->getGroups(), true);  // ← strict comparison
}
```

Parameter ketiga `true` pada `in_array()` artinya **strict comparison**:
- `'pengurus' === 'Pengurus'` → **FALSE**
- Harus exact match!

### Mengapa Database Bisa Store Title Case?

Kemungkinan sumber Title case di database:

1. **Manual INSERT via phpMyAdmin:**
   ```sql
   INSERT INTO auth_groups_users (user_id, group)
   VALUES (1, 'Pengurus');  -- ← Typo Title case
   ```

2. **Code yang lama sebelum standarisasi:**
   ```php
   // Code lama yang salah
   $userModel->addToGroup($userId, 'Pengurus');  // ← Should be 'pengurus'
   ```

3. **Import dari Excel/CSV:**
   Data import punya Title case, tidak di-lowercase dulu.

### Best Practice:

**SELALU gunakan lowercase untuk group key:**
```php
// ✅ BENAR
$user->addToGroup('pengurus');
$user->inGroup('pengurus');

// ❌ SALAH
$user->addToGroup('Pengurus');
$user->inGroup('Pengurus');
```

---

## 📞 Support

Jika setelah mengikuti semua langkah masalah masih belum solved:

1. **Screenshot** halaman setelah login (URL + sidebar)
2. **Copy hasil** query diagnostic lengkap
3. **Share** error log dari `writable/logs/log-YYYY-MM-DD.log`
4. **Info** browser yang digunakan

---

## ✅ Checklist

Setelah fix ini dijalankan:

- [ ] Query diagnostic menunjukkan semua group sudah lowercase
- [ ] User Pengurus logout dan clear cache
- [ ] Login ulang sebagai Pengurus
- [ ] Redirect ke `/admin/dashboard` ✅
- [ ] Sidebar menampilkan menu admin ✅
- [ ] Menu "Calon Anggota", "Pembayaran", "Statistik" muncul ✅

---

**Last Updated:** 2025-01-30
**Version:** 1.0.0
**Status:** Production Ready ✅
