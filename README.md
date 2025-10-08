# SPK.OR.ID - Sistem Informasi Keanggotaan SPK

Platform digital yang komprehensif, terstruktur, dan efisien untuk Serikat Pekerja Kampus (SPK). Visi utamanya adalah menciptakan alat perjuangan digital yang berfungsi sebagai pusat data keanggotaan, media advokasi, serta sarana komunikasi dan layanan internal bagi seluruh pekerja di sektor pendidikan tinggi.

## 🚀 Fitur Utama

### Sistem Otentikasi & Otorisasi
- **RBAC (Role-Based Access Control)** - Kontrol akses berbasis peran
- **ABAC (Attribute-Based Access Control)** - Kontrol akses berbasis atribut (wilayah)
- **5 Tingkat Peran Pengguna:**
  - Super Admin - Akses penuh ke seluruh sistem
  - Pengurus - Manajemen anggota dan konten
  - Koordinator Wilayah - Akses terbatas per wilayah regional
  - Anggota - Portal anggota dengan fitur lengkap
  - Calon - Akses terbatas untuk calon anggota
- Manajemen menu dinamis per peran
- Manajemen permission dinamis

### Manajemen Keanggotaan
- **Alur Pendaftaran Lengkap:**
  - Formulir pendaftaran online
  - Verifikasi data oleh admin
  - Persetujuan keanggotaan
  - Aktivasi akun member
- **Manajemen Anggota:**
  - CRUD anggota dengan data lengkap
  - Import bulk data anggota lama (CSV/Excel)
  - Export data anggota
  - Filter dan pencarian advanced
- **Kontrol Akses Regional:**
  - Koordinator Wilayah hanya dapat mengakses data anggota di wilayahnya
  - Super Admin & Pengurus dapat mengakses semua wilayah

### Portal Anggota
- Dashboard anggota dengan informasi lengkap
- **Kartu Anggota Digital:**
  - Generate PDF kartu anggota
  - QR Code untuk verifikasi
  - Download kartu dalam format PDF
- Profil anggota yang dapat diedit
- Riwayat aktivitas

### Forum Diskusi
- Kategori forum yang terorganisir
- Buat topik diskusi
- Reply dan interaksi antar anggota
- Pin dan lock topik (untuk admin)
- Counter views topik

### Sistem Survey
- Buat survey dengan berbagai tipe pertanyaan:
  - Text input
  - Textarea
  - Single choice
  - Multiple choice
  - Rating scale
- Survey anonim atau teridentifikasi
- Periode aktif survey
- Laporan hasil survey
- Export hasil survey

### Sistem Pengaduan (Ticketing)
- Submit pengaduan dengan kategori
- Sistem prioritas (Low, Medium, High, Urgent)
- Status tracking (Open, In Progress, Resolved, Closed)
- Assignment tiket ke staff
- Komunikasi melalui replies
- Internal notes untuk staff

### Administrasi Super Admin
- Dashboard statistik sistem
- Manajemen user dan role
- Manajemen permission
- Manajemen wilayah/region
- Konfigurasi menu dinamis
- Activity logs sistem
- Laporan dan statistik

## 🛠️ Teknologi

- **Framework:** CodeIgniter 4.6.3
- **PHP:** >= 8.1
- **Database:** MySQL/MariaDB
- **Libraries:**
  - endroid/qr-code - Generate QR codes
  - dompdf/dompdf - Generate PDF documents
- **Frontend:** Bootstrap 5, jQuery, Font Awesome

## 📋 Persyaratan Sistem

- PHP 8.1 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Composer
- Apache/Nginx web server
- PHP Extensions:
  - intl
  - mbstring
  - json
  - mysqlnd
  - xml
  - gd

## 🔧 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/yoezron/spk.or.id.git
cd spk.or.id
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

```bash
cp env .env
```

Edit file `.env` dan sesuaikan konfigurasi database:

```
# Database Configuration
database.default.hostname = localhost
database.default.database = spk_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

# Application
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
```

### 4. Buat Database

```bash
mysql -u root -p
CREATE DATABASE spk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Jalankan Migrasi & Seeder

```bash
php spark migrate
php spark db:seed InitialDataSeeder
```

### 6. Jalankan Aplikasi

```bash
php spark serve
```

Aplikasi akan berjalan di `http://localhost:8080`

## 👤 Default Login

Setelah menjalankan seeder, gunakan kredensial berikut:

- **Username:** admin
- **Password:** admin123
- **Role:** Super Admin

⚠️ **Penting:** Segera ubah password default setelah login pertama!

## 📁 Struktur Database

### Tabel Utama

1. **roles** - Menyimpan role/peran pengguna
2. **permissions** - Menyimpan permission sistem
3. **role_permissions** - Relasi many-to-many role dan permission
4. **users** - Data pengguna sistem
5. **regions** - Data wilayah/regional
6. **members** - Data lengkap anggota SPK
7. **menus** - Menu navigasi sistem
8. **role_menus** - Relasi role dan menu

### Tabel Forum

- **forum_categories** - Kategori forum
- **forum_topics** - Topik diskusi
- **forum_replies** - Balasan diskusi

### Tabel Survey

- **surveys** - Data survey
- **survey_questions** - Pertanyaan survey
- **survey_responses** - Response survey
- **survey_answers** - Jawaban detail

### Tabel Ticketing

- **tickets** - Data pengaduan
- **ticket_replies** - Balasan/komunikasi tiket

### Tabel Lainnya

- **activity_logs** - Log aktivitas sistem

## 🔐 Sistem RBAC + ABAC

### RBAC (Role-Based Access Control)
Setiap user memiliki role yang menentukan permission mereka di sistem.

### ABAC (Attribute-Based Access Control)
Koordinator Wilayah memiliki attribute `region_id` yang membatasi akses mereka hanya pada data anggota di wilayah tersebut.

### Permission Structure

Format permission: `{action}-{resource}`

Contoh:
- `view-members` - Lihat daftar anggota
- `create-members` - Tambah anggota baru
- `edit-members` - Edit data anggota
- `delete-members` - Hapus anggota

## 📝 Import Data Anggota

Sistem mendukung bulk import data anggota lama melalui file CSV/Excel dengan format:

```csv
nama_lengkap,email,nik,tempat_lahir,tanggal_lahir,jenis_kelamin,alamat,wilayah,tempat_kerja,posisi
```

## 🎨 Kustomisasi

### Menambah Permission Baru

Edit file seeder atau gunakan interface admin untuk menambah permission baru.

### Menambah Menu

Gunakan interface Menu Management di admin panel untuk menambah atau mengubah struktur menu.

### Menambah Wilayah

Gunakan interface Wilayah di admin panel untuk menambah wilayah baru.

## 📸 QR Code & PDF Kartu Anggota

Sistem otomatis generate:
- QR Code unik per anggota
- PDF kartu anggota dengan QR Code
- Download kartu melalui portal anggota

## 📊 Activity Logs

Semua aktivitas penting di sistem tercatat:
- Login/Logout
- CRUD operations
- Approval/Rejection
- Permission changes

## 🤝 Kontribusi

Kontribusi untuk pengembangan sistem ini sangat diterima. Silakan:

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Distributed under the MIT License. See `LICENSE` for more information.

## 📧 Kontak

SPK (Serikat Pekerja Kampus)
- Website: https://spk.or.id
- Email: info@spk.or.id

## 🙏 Acknowledgements

- CodeIgniter 4 Framework
- Bootstrap UI Framework
- Font Awesome Icons
- DomPDF Library
- Endroid QR Code Library
