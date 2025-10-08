# Database Schema Documentation

## Overview

Sistem Informasi Keanggotaan SPK menggunakan database relational MySQL/MariaDB dengan struktur yang mendukung RBAC (Role-Based Access Control) dan ABAC (Attribute-Based Access Control).

## Core Tables

### 1. roles
Menyimpan informasi tentang peran/role dalam sistem.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| name | VARCHAR(100) | Nama role |
| slug | VARCHAR(100) | Slug unik untuk role |
| description | TEXT | Deskripsi role |
| level | INT(3) | Level hierarki role (0-4) |
| is_active | TINYINT(1) | Status aktif role |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (slug)

**Default Roles:**
1. Super Admin (level 4)
2. Pengurus (level 3)
3. Koordinator Wilayah (level 2)
4. Anggota (level 1)
5. Calon (level 0)

---

### 2. permissions
Menyimpan daftar permission yang tersedia di sistem.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| name | VARCHAR(100) | Nama permission |
| slug | VARCHAR(100) | Slug unik permission |
| resource | VARCHAR(100) | Resource yang dikontrol |
| action | VARCHAR(50) | Aksi yang diizinkan |
| description | TEXT | Deskripsi permission |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (slug)

**Permission Format:** `{action}-{resource}`
**Example:** view-members, create-members, edit-members

---

### 3. role_permissions
Tabel pivot untuk relasi many-to-many antara roles dan permissions.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| role_id | INT(11) | Foreign Key ke roles |
| permission_id | INT(11) | Foreign Key ke permissions |
| created_at | DATETIME | Timestamp pembuatan |

**Foreign Keys:**
- role_id → roles(id) ON DELETE CASCADE
- permission_id → permissions(id) ON DELETE CASCADE

---

### 4. regions
Menyimpan informasi wilayah/regional untuk ABAC.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| name | VARCHAR(200) | Nama wilayah |
| code | VARCHAR(50) | Kode unik wilayah |
| province | VARCHAR(100) | Nama provinsi |
| city | VARCHAR(100) | Nama kota |
| description | TEXT | Deskripsi wilayah |
| coordinator_id | INT(11) | User ID koordinator |
| is_active | TINYINT(1) | Status aktif |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (code)

---

### 5. users
Menyimpan informasi pengguna sistem.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| username | VARCHAR(100) | Username unik |
| email | VARCHAR(255) | Email unik |
| password | VARCHAR(255) | Password hash (bcrypt) |
| role_id | INT(11) | Foreign Key ke roles |
| region_id | INT(11) | Foreign Key ke regions (ABAC) |
| full_name | VARCHAR(255) | Nama lengkap |
| phone | VARCHAR(20) | Nomor telepon |
| avatar | VARCHAR(255) | Path file avatar |
| is_active | TINYINT(1) | Status aktif |
| last_login | DATETIME | Timestamp login terakhir |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |
| deleted_at | DATETIME | Soft delete timestamp |

**Foreign Keys:**
- role_id → roles(id) ON DELETE CASCADE
- region_id → regions(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (username)
- UNIQUE (email)

---

### 6. members
Menyimpan data lengkap anggota SPK.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| user_id | INT(11) | Foreign Key ke users |
| member_number | VARCHAR(50) | Nomor anggota unik |
| nik | VARCHAR(20) | NIK/KTP |
| birth_place | VARCHAR(100) | Tempat lahir |
| birth_date | DATE | Tanggal lahir |
| gender | ENUM('L','P') | Jenis kelamin |
| address | TEXT | Alamat lengkap |
| region_id | INT(11) | Foreign Key ke regions |
| workplace | VARCHAR(255) | Tempat kerja |
| position | VARCHAR(100) | Jabatan |
| join_date | DATE | Tanggal bergabung |
| status | ENUM | Status keanggotaan |
| registration_status | ENUM | Status pendaftaran |
| card_photo | VARCHAR(255) | Path foto untuk kartu |
| qr_code | VARCHAR(255) | Path QR code |
| notes | TEXT | Catatan |
| approved_by | INT(11) | User ID yang approve |
| approved_at | DATETIME | Timestamp approval |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |
| deleted_at | DATETIME | Soft delete timestamp |

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE
- region_id → regions(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (member_number)

**Status Values:**
- calon - Calon anggota
- anggota - Anggota aktif
- non_aktif - Anggota non-aktif

**Registration Status Values:**
- pending - Menunggu verifikasi
- verified - Terverifikasi
- approved - Disetujui
- rejected - Ditolak

---

## Menu Management Tables

### 7. menus
Menyimpan struktur menu navigasi sistem.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| parent_id | INT(11) | Parent menu ID |
| name | VARCHAR(100) | Nama menu |
| url | VARCHAR(255) | URL tujuan |
| icon | VARCHAR(50) | Icon class (Font Awesome) |
| order | INT(11) | Urutan tampilan |
| is_active | TINYINT(1) | Status aktif |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

---

### 8. role_menus
Tabel pivot untuk relasi roles dan menus.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| role_id | INT(11) | Foreign Key ke roles |
| menu_id | INT(11) | Foreign Key ke menus |
| created_at | DATETIME | Timestamp pembuatan |

**Foreign Keys:**
- role_id → roles(id) ON DELETE CASCADE
- menu_id → menus(id) ON DELETE CASCADE

---

## Forum Tables

### 9. forum_categories
Menyimpan kategori forum diskusi.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| name | VARCHAR(200) | Nama kategori |
| slug | VARCHAR(200) | Slug unik |
| description | TEXT | Deskripsi kategori |
| is_active | TINYINT(1) | Status aktif |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

---

### 10. forum_topics
Menyimpan topik diskusi forum.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| category_id | INT(11) | Foreign Key ke forum_categories |
| user_id | INT(11) | Foreign Key ke users |
| title | VARCHAR(255) | Judul topik |
| slug | VARCHAR(255) | Slug unik |
| content | TEXT | Konten topik |
| views | INT(11) | Jumlah views |
| is_pinned | TINYINT(1) | Status pin |
| is_locked | TINYINT(1) | Status lock |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |
| deleted_at | DATETIME | Soft delete timestamp |

**Foreign Keys:**
- category_id → forum_categories(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE

---

### 11. forum_replies
Menyimpan balasan/komentar di topik forum.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| topic_id | INT(11) | Foreign Key ke forum_topics |
| user_id | INT(11) | Foreign Key ke users |
| content | TEXT | Konten balasan |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |
| deleted_at | DATETIME | Soft delete timestamp |

**Foreign Keys:**
- topic_id → forum_topics(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE

---

## Survey Tables

### 12. surveys
Menyimpan data survey.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| title | VARCHAR(255) | Judul survey |
| description | TEXT | Deskripsi survey |
| created_by | INT(11) | Foreign Key ke users |
| start_date | DATETIME | Tanggal mulai |
| end_date | DATETIME | Tanggal berakhir |
| is_active | TINYINT(1) | Status aktif |
| is_anonymous | TINYINT(1) | Survey anonim? |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

**Foreign Keys:**
- created_by → users(id) ON DELETE CASCADE

---

### 13. survey_questions
Menyimpan pertanyaan survey.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| survey_id | INT(11) | Foreign Key ke surveys |
| question | TEXT | Teks pertanyaan |
| type | ENUM | Tipe pertanyaan |
| options | TEXT | Opsi jawaban (JSON) |
| is_required | TINYINT(1) | Wajib diisi? |
| order | INT(11) | Urutan pertanyaan |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

**Foreign Keys:**
- survey_id → surveys(id) ON DELETE CASCADE

**Question Types:**
- text - Input text
- textarea - Textarea
- single_choice - Pilihan tunggal
- multiple_choice - Pilihan ganda
- rating - Rating scale

---

### 14. survey_responses
Menyimpan response survey per user.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| survey_id | INT(11) | Foreign Key ke surveys |
| user_id | INT(11) | Foreign Key ke users |
| created_at | DATETIME | Timestamp pembuatan |

**Foreign Keys:**
- survey_id → surveys(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE SET NULL (nullable for anonymous)

---

### 15. survey_answers
Menyimpan jawaban detail survey.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| response_id | INT(11) | Foreign Key ke survey_responses |
| question_id | INT(11) | Foreign Key ke survey_questions |
| answer | TEXT | Jawaban |
| created_at | DATETIME | Timestamp pembuatan |

**Foreign Keys:**
- response_id → survey_responses(id) ON DELETE CASCADE
- question_id → survey_questions(id) ON DELETE CASCADE

---

## Ticketing Tables

### 16. tickets
Menyimpan data pengaduan/tiket.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| ticket_number | VARCHAR(50) | Nomor tiket unik |
| user_id | INT(11) | Foreign Key ke users |
| category | VARCHAR(100) | Kategori pengaduan |
| subject | VARCHAR(255) | Subjek |
| description | TEXT | Deskripsi lengkap |
| priority | ENUM | Prioritas tiket |
| status | ENUM | Status tiket |
| assigned_to | INT(11) | User ID staff |
| resolved_by | INT(11) | User ID penyelesai |
| resolved_at | DATETIME | Timestamp resolved |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |
| deleted_at | DATETIME | Soft delete timestamp |

**Foreign Keys:**
- user_id → users(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (ticket_number)

**Priority Values:**
- low
- medium
- high
- urgent

**Status Values:**
- open - Terbuka
- in_progress - Sedang diproses
- resolved - Selesai
- closed - Ditutup

---

### 17. ticket_replies
Menyimpan komunikasi/balasan tiket.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| ticket_id | INT(11) | Foreign Key ke tickets |
| user_id | INT(11) | Foreign Key ke users |
| message | TEXT | Pesan balasan |
| is_internal | TINYINT(1) | Internal note (staff only) |
| created_at | DATETIME | Timestamp pembuatan |
| updated_at | DATETIME | Timestamp update |

**Foreign Keys:**
- ticket_id → tickets(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE

---

## Activity Logs

### 18. activity_logs
Menyimpan log aktivitas sistem untuk audit trail.

| Column | Type | Description |
|--------|------|-------------|
| id | INT(11) | Primary Key |
| user_id | INT(11) | Foreign Key ke users |
| action | VARCHAR(100) | Aksi yang dilakukan |
| resource | VARCHAR(100) | Resource yang diakses |
| resource_id | INT(11) | ID resource |
| description | TEXT | Deskripsi aktivitas |
| ip_address | VARCHAR(45) | IP address |
| user_agent | TEXT | User agent browser |
| created_at | DATETIME | Timestamp aktivitas |

**Foreign Keys:**
- user_id → users(id) ON DELETE SET NULL

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id, created_at)

---

## Entity Relationship Diagram

```
roles ──┬─< role_permissions >── permissions
        │
        ├─< users ──┬─< members
        │           │
        │           ├─< forum_topics ── forum_replies
        │           │
        │           ├─< survey_responses >── survey_answers
        │           │
        │           ├─< tickets ── ticket_replies
        │           │
        │           └─< activity_logs
        │
        └─< role_menus >── menus

regions ──┬─< users
          │
          └─< members

forum_categories ──< forum_topics

surveys ──< survey_questions
        └─< survey_responses

survey_questions ──< survey_answers
```

---

## Database Naming Conventions

1. **Tables:** Plural, lowercase, snake_case
2. **Columns:** Singular, lowercase, snake_case
3. **Primary Keys:** id
4. **Foreign Keys:** {table_singular}_id
5. **Timestamps:** created_at, updated_at, deleted_at
6. **Booleans:** is_{something}

---

## Indexes Strategy

1. Primary keys pada semua tabel
2. Unique indexes pada kolom yang harus unik (username, email, member_number, dll)
3. Foreign key indexes untuk optimasi join
4. Composite indexes pada query yang sering digunakan (user_id + created_at pada activity_logs)

---

## Data Integrity

1. Foreign key constraints dengan ON DELETE CASCADE atau SET NULL sesuai kebutuhan
2. ENUM untuk kolom dengan nilai terbatas
3. NOT NULL constraints untuk kolom wajib
4. DEFAULT values untuk kolom dengan nilai default
5. Soft deletes (deleted_at) untuk data penting yang perlu audit trail

---

## Security Considerations

1. Password disimpan dengan bcrypt hash
2. Sensitive data (NIK) dapat dienkrip di application layer
3. Activity logs untuk audit trail
4. Soft deletes untuk data recovery
5. IP address dan user agent logging

---

## Performance Optimization

1. Indexes pada foreign keys
2. Composite indexes untuk query kompleks
3. Partitioning untuk tabel besar (activity_logs)
4. Query optimization dengan proper joins
5. Caching strategy di application layer
