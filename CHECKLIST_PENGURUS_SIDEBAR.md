# ✅ Checklist: Sidebar Menu untuk Role Pengurus

## 🔍 Masalah

Ada 2 masalah utama yang mungkin dialami:

### Masalah A: Login Redirect ke Dashboard yang Salah
- User login sebagai **Pengurus** tapi di-redirect ke `/member/dashboard`
- Seharusnya di-redirect ke `/admin/dashboard`
- **Lihat**: `FIX_PENGURUS_LOGIN_REDIRECT.md` untuk solusi

### Masalah B: Menu Tidak Muncul di Sidebar
- Role Pengurus sudah memiliki **57 permissions** yang di-assign
- Tapi tidak semua menu muncul di sidebar

---

## 📊 Data Permission Pengurus
Berdasarkan tabel `auth_groups_permissions`:
- **group_id = 2** (Pengurus)
- **Total permissions:** 57
- **Permission IDs:** 1-11, 27-56, 63-74, 79-82

---

## 🔧 Langkah Diagnosis

### STEP 0: ⚠️ CEK LOGIN REDIRECT DULU (PALING PENTING!)

**Masalah:** Login sebagai Pengurus tapi masuk ke `/member/dashboard`

**Quick Check:**
1. Login sebagai user dengan role Pengurus
2. Perhatikan URL setelah login:
   - ❌ Jika: `http://localhost:8080/member/dashboard` → **ADA MASALAH!**
   - ✅ Jika: `http://localhost:8080/admin/dashboard` → **OK, lanjut STEP 1**

**Jika redirect SALAH:**
👉 **STOP!** Baca dan ikuti: **`FIX_PENGURUS_LOGIN_REDIRECT.md`**

**Mengapa ini penting?**
Karena kalau redirect salah, sidebar yang muncul adalah **member sidebar**, bukan **admin sidebar**. Jadi meskipun permission sudah benar, menu admin tetap tidak akan muncul!

---

### STEP 1: Cek Apakah Tabel Menus Sudah Terisi

**Jalankan query ini di phpMyAdmin:**
```sql
SELECT COUNT(*) as total_menus FROM menus;
```

**Expected Result:**
- ✅ **28 atau lebih** = Menu sudah di-populate
- ❌ **0** = Tabel kosong, perlu di-populate
- ⚠️ **1-27** = Data menu tidak lengkap

**Jika hasilnya 0 atau kurang dari 20:**
👉 Lanjut ke **STEP 2** untuk populate menu

**Jika hasilnya 28+:**
👉 Lanjut ke **STEP 3** untuk verifikasi permission keys

---

### STEP 2: Populate Tabel Menus (WAJIB jika tabel kosong)

1. Buka **phpMyAdmin**
2. Pilih database `spk_db`
3. Klik tab **SQL**
4. Buka file `/home/user/spk-app/database/sql/seed_menus.sql`
5. **Copy SELURUH isi** file tersebut
6. **Paste** di SQL editor phpMyAdmin
7. Klik **Go/Jalankan**
8. Verify: `SELECT COUNT(*) FROM menus;` hasilnya harus **28**

**Screenshot yang diharapkan:**
```
Query OK, 28 rows affected
Total menus inserted: 28
Parent menus: 17
Child menus: 11
```

---

### STEP 3: Verifikasi Permission Keys Match

**Jalankan query diagnostic lengkap:**
1. Buka file `/home/user/spk-app/database/sql/diagnostic_pengurus_menus.sql`
2. Copy dan jalankan di phpMyAdmin
3. Perhatikan hasil query **#4** (CROSS-CHECK)

**Jika ada hasil di query #4:**
- Artinya: Ada permissions yang dimiliki Pengurus tapi TIDAK ADA menu untuk permission tersebut
- Ini **NORMAL** karena tidak semua permission harus punya menu
- Contoh: Permission `member.delete`, `member.edit` tidak perlu menu sendiri (aksi saja)

**Jika query #5 menunjukkan banyak menu HIDDEN:**
- Periksa apakah permission_key di menu cocok dengan permission yang dimiliki Pengurus
- Assign permission yang kurang

---

### STEP 4: Logout & Login Ulang

Setelah populate menu:
1. **Logout** dari aplikasi
2. **Clear browser cache** (Ctrl+Shift+Delete)
3. **Login ulang** sebagai user dengan role "Pengurus"
4. **Refresh halaman** (Ctrl+F5)

---

## 📋 Checklist Menu yang Seharusnya Muncul

Dengan 57 permissions, Pengurus seharusnya melihat menu berikut:

### ✅ Dashboard & Statistik
- [ ] Dashboard (permission: `admin.dashboard`)
- [ ] Statistik & Laporan (permission: `statistics.view`)

### ✅ Manajemen Anggota
- [ ] Kelola Anggota (parent menu)
  - [ ] Daftar Anggota (permission: `member.view`)
  - [ ] Calon Anggota (permission: `member.approve`) ← **INI HARUS MUNCUL**
  - [ ] Export Data (permission: `member.export`)
- [ ] Import Anggota (permission: `member.import`)

### ✅ Keuangan
- [ ] Pembayaran (parent menu)
  - [ ] Daftar Pembayaran (permission: `payment.view`)
  - [ ] Perlu Verifikasi (permission: `payment.verify`)
  - [ ] Laporan Keuangan (permission: `payment.report`)

### ✅ Struktur Organisasi
- [ ] Struktur Organisasi (parent menu)
  - [ ] Lihat Struktur (permission: `org_structure.view`)
  - [ ] Kelola Jabatan (permission: `org_structure.manage`)
  - [ ] Penugasan (permission: `org_structure.assign`)

### ✅ Komunitas
- [ ] Moderasi Forum (permission: `forum.moderate`)
- [ ] Kelola Survei (parent menu)
  - [ ] Daftar Survei (permission: `survey.manage`)
  - [ ] Buat Survei Baru (permission: `survey.create`)
  - [ ] Lihat Respon (permission: `survey.view_results`)
- [ ] Pengaduan (permission: `complaint.view`)
- [ ] WhatsApp Groups (permission: `wa_group.manage`)

### ✅ Konten
- [ ] Konten & Blog (parent menu)
  - [ ] Artikel/Blog (permission: `content.manage`)
  - [ ] Halaman Statis (permission: `content.manage`)
  - [ ] Kategori (permission: `content.manage`)

**Total menu yang seharusnya muncul:** ~25 menu items

---

## 🐛 Troubleshooting

### Masalah 1: Semua menu tidak muncul, sidebar kosong
**Penyebab:** Tabel menus kosong
**Solusi:** Jalankan STEP 2 (populate menu)

### Masalah 2: Hanya beberapa menu yang muncul
**Penyebab:**
- Menu belum di-populate lengkap, ATAU
- Permission key di menu tidak cocok dengan permission yang dimiliki

**Solusi:**
1. Jalankan query diagnostic (#5) untuk lihat menu mana yang HIDDEN
2. Check permission_key di menu tersebut
3. Pastikan permission tersebut sudah di-assign ke Pengurus

### Masalah 3: Menu "Calon Anggota" tidak muncul
**Penyebab:** Permission `member.approve` belum di-assign atau menu belum ada

**Cek permission:**
```sql
SELECT ap.name
FROM auth_groups_permissions agp
JOIN auth_permissions ap ON ap.id = agp.permission_id
WHERE agp.group_id = 2 AND ap.name = 'member.approve';
```

Jika hasil kosong, assign permission:
```sql
INSERT INTO auth_groups_permissions (group_id, permission_id, created_at)
SELECT 2, id, NOW()
FROM auth_permissions
WHERE name = 'member.approve';
```

**Cek menu:**
```sql
SELECT * FROM menus WHERE permission_key = 'member.approve';
```

Jika hasil kosong, berarti menu belum di-populate.

### Masalah 4: Menu muncul tapi tidak bisa diklik
**Penyebab:** URL menu salah atau controller belum dibuat

**Cek:**
```sql
SELECT id, title, url FROM menus WHERE url = '#' OR url IS NULL OR url = '';
```

Menu dengan URL '#' adalah parent menu yang punya children, ini normal.

---

## 🔍 Query Diagnostic Lengkap

Untuk mengecek semuanya sekaligus, jalankan:
```bash
# Di phpMyAdmin, jalankan file:
/home/user/spk-app/database/sql/diagnostic_pengurus_menus.sql
```

Query ini akan menampilkan:
1. Total menu di database
2. Permission apa saja yang dimiliki Pengurus
3. Menu apa saja yang ada
4. Cross-check permission vs menu
5. Menu mana yang visible vs hidden untuk Pengurus
6. Summary berapa menu yang seharusnya muncul

---

## ✅ Expected Final Result

Setelah semua langkah di atas:

**Ketika login sebagai Pengurus, sidebar menampilkan:**
```
Dashboard
└─ Dashboard

Kelola Anggota
├─ Daftar Anggota
├─ Calon Anggota          ✅ MUNCUL
└─ Export Data

Import Anggota

Statistik & Laporan

Pembayaran
├─ Daftar Pembayaran
├─ Perlu Verifikasi
└─ Laporan Keuangan

Struktur Organisasi
├─ Lihat Struktur
├─ Kelola Jabatan
└─ Penugasan

Moderasi Forum

Kelola Survei
├─ Daftar Survei
├─ Buat Survei Baru
└─ Lihat Respon

Pengaduan

WhatsApp Groups

Konten & Blog
├─ Artikel/Blog
├─ Halaman Statis
└─ Kategori

Akun
├─ Ubah Password
└─ Logout
```

**Total:** ~25+ menu items visible

---

## 📞 Support

Jika setelah mengikuti semua langkah menu masih tidak muncul:

1. **Screenshot** sidebar yang muncul
2. **Copy hasil** query diagnostic (#8)
3. **Share** ke developer dengan info:
   - Berapa menu yang muncul vs yang seharusnya muncul
   - Screenshot hasil query diagnostic
   - Browser yang digunakan

---

**Last Updated:** 2025-01-30
**Version:** 1.0.0
