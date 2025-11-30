# Dynamic Sidebar dengan Role & Permission System

## Ringkasan Perubahan

Sistem sidebar sekarang telah diperbaiki untuk **menampilkan menu secara dinamis berdasarkan permission yang diberikan oleh superadmin kepada role pengurus**. Perubahan ini memastikan bahwa:

1. ✅ Sidebar hanya menampilkan menu yang boleh diakses oleh user berdasarkan permission mereka
2. ✅ Superadmin dapat mengelola permission untuk setiap role melalui database
3. ✅ Menu bersifat hierarkis (parent-child) dan dinamis dari database
4. ✅ Permission filtering berfungsi di level service dan component

---

## File yang Diubah

### 1. **app/Views/components/sidebar.php**
**Perubahan:**
- ✅ Memperbaiki field permission dari `required_permission` → `permission_key` (sesuai database)
- ✅ Menambahkan konversi array ke object untuk kompatibilitas
- ✅ Menyederhanakan rendering icon Material Icons

**Kode Sebelum:**
```php
if (!empty($item->required_permission)) {
    if (!auth()->user()->can($item->required_permission)) {
        return '';
    }
}
```

**Kode Sesudah:**
```php
// Convert array to object if necessary
if (is_array($item)) {
    $item = (object) $item;
}

// Check permission if required - use permission_key field
if (!empty($item->permission_key)) {
    if (!auth()->user()->can($item->permission_key)) {
        return '';
    }
}
```

---

### 2. **app/Services/MenuService.php**
**Perubahan:**
- ✅ Method `getMenuForUser()` sekarang mengembalikan **object** bukan array
- ✅ Method `getMenuTree()` juga mengembalikan object untuk konsistensi
- ✅ Permission filtering dilakukan di level service sebelum data dikirim ke view

**Kode Sebelum:**
```php
$menuItem = (array) $menu;
// ...
$menuItem['children'] = $children['data'];
```

**Kode Sesudah:**
```php
// Keep menu as object for consistency
$menuItem = clone $menu;
// ...
$menuItem->children = $children['data'];
```

---

### 3. **app/Database/Seeds/MenuSeeder.php** (Baru)
**Deskripsi:**
- ✅ File seeder baru untuk populate data menu ke database
- ✅ Menu terhubung dengan **permission keys** yang ada di `auth_permissions`
- ✅ Struktur hierarkis (parent-child) sudah diatur dengan `parent_id`

**Contoh Data:**
```php
[
    'id' => 10,
    'parent_id' => null,
    'title' => 'Kelola Anggota',
    'url' => '#',
    'icon' => 'group',
    'permission_key' => 'member.view', // ← Link ke permission
    'is_active' => 1,
    'sort_order' => 10,
],
[
    'id' => 11,
    'parent_id' => 10, // ← Child dari menu ID 10
    'title' => 'Daftar Anggota',
    'url' => 'admin/members',
    'icon' => 'list',
    'permission_key' => 'member.view',
    'is_active' => 1,
    'sort_order' => 1,
],
```

---

## Cara Kerja Sistem

### Flow Permission Check

```
User Login
    ↓
User memiliki Role (e.g., "Pengurus")
    ↓
Role memiliki Permissions (e.g., "member.view", "member.edit")
    ↓
MenuService::getMenuForUser() dipanggil
    ↓
Query database untuk menu yang aktif (is_active = 1)
    ↓
Filter menu berdasarkan permission_key:
    - Jika menu punya permission_key: Cek apakah user->can(permission_key)
    - Jika tidak punya permission_key: Menu publik (ditampilkan semua user)
    ↓
Build tree hierarki (parent-child)
    ↓
Return filtered menu ke sidebar
    ↓
Sidebar render hanya menu yang di-return
```

---

## Cara Menggunakan

### 1. Populate Data Menu (Opsional jika sudah ada data)

Jalankan seeder untuk mengisi tabel `menus`:

```bash
php spark db:seed MenuSeeder
```

### 2. Memberikan Permission ke Role

**Cara 1: Via Superadmin Dashboard** (Recommended)
1. Login sebagai superadmin
2. Buka menu **Super Admin → Role & Permission**
3. Pilih role "Pengurus"
4. Centang/check permission yang ingin diberikan:
   - `member.view` - Untuk melihat menu "Kelola Anggota"
   - `member.edit` - Untuk edit anggota
   - `payment.view` - Untuk melihat menu "Pembayaran"
   - dll.
5. Save changes

**Cara 2: Via Database** (Manual)
```sql
-- Ambil ID role
SELECT id FROM auth_groups WHERE title = 'Pengurus';
-- Misal hasilnya: 3

-- Ambil ID permission
SELECT id FROM auth_permissions WHERE name = 'member.view';
-- Misal hasilnya: 10

-- Assign permission ke role
INSERT INTO auth_groups_permissions (group_id, permission_id, created_at)
VALUES (3, 10, NOW());
```

---

### 3. Menggunakan Dynamic Sidebar di Layout

Update file layout (e.g., `app/Views/layouts/admin.php`) untuk menggunakan sidebar component:

**Cara 1: Menggunakan Component Sidebar (Recommended)**
```php
<?php
// Di dalam <div class="app-sidebar">
$currentUser = auth()->user();
$sidebarType = 'admin'; // atau 'super', 'member'

echo view('components/sidebar', [
    'currentUser' => $currentUser,
    'sidebarType' => $sidebarType
]);
?>
```

**Cara 2: Inline Permission Checks (Current Method)**
```php
<?php if (auth()->user()->can('member.view')): ?>
    <li>
        <a href="<?= base_url('admin/members') ?>">
            <i class="material-icons-two-tone">group</i>Kelola Anggota
        </a>
    </li>
<?php endif; ?>
```

---

## Testing Permission System

### Test Case 1: User dengan Full Permissions
```bash
# Login sebagai user dengan role "Pengurus" yang punya semua permission
# Expected: Semua menu muncul
```

### Test Case 2: User dengan Partial Permissions
```bash
# Superadmin remove permission "payment.verify" dari role "Pengurus"
# Login sebagai pengurus
# Expected: Menu "Perlu Verifikasi" di bawah "Pembayaran" tidak muncul
```

### Test Case 3: User dengan No Permissions
```bash
# Superadmin remove semua permission dari role tertentu
# Login sebagai user dengan role tersebut
# Expected: Hanya menu publik (tanpa permission_key) yang muncul
```

---

## Permission Keys yang Tersedia

Berikut daftar permission yang sudah didefinisikan di `AuthGroups.php` dan dapat diassign ke menu:

### Manajemen Anggota
- `member.view` - Melihat daftar anggota
- `member.edit` - Edit data anggota
- `member.delete` - Hapus anggota
- `member.approve` - Approve calon anggota
- `member.import` - Import bulk anggota
- `member.export` - Export data anggota
- `member.manage` - Full akses manajemen anggota

### Dashboard
- `admin.dashboard` - Akses dashboard admin
- `dashboard.admin` - Alias untuk admin.dashboard

### Pembayaran
- `payment.view` - Melihat pembayaran
- `payment.verify` - Verifikasi pembayaran
- `payment.report` - Lihat laporan keuangan
- `payment.export` - Export data pembayaran

### Pengaduan/Ticket
- `complaint.view` - Melihat pengaduan
- `complaint.manage` - Kelola pengaduan
- `ticket.view` - Alias untuk complaint.view

### Konten
- `content.manage` - Kelola konten (posts/pages)
- `content.create` - Buat konten baru

### Forum
- `forum.moderate` - Moderasi forum

### Struktur Organisasi
- `org_structure.view` - Lihat struktur organisasi
- `org_structure.manage` - Kelola struktur organisasi
- `org_structure.assign` - Assign posisi/jabatan

### Statistik
- `statistics.view` - Lihat statistik
- `statistics.export` - Export statistik
- `stats.view` - Alias
- `stats.export` - Alias

### Survei
- `survey.manage` - Kelola survei
- `survey.create` - Buat survei baru
- `survey.view_results` - Lihat hasil survei

### WhatsApp Groups
- `wagroup.manage` - Kelola grup WhatsApp
- `wa_group.manage` - Alias

---

## Menambah Menu Baru

Untuk menambah menu baru ke database:

```php
// Via Database Seeder atau SQL
INSERT INTO menus (
    parent_id,
    title,
    url,
    route_name,
    icon,
    permission_key,
    is_active,
    sort_order,
    created_at,
    updated_at
) VALUES (
    NULL,                      -- parent_id (NULL = top level)
    'Menu Baru',              -- title
    'admin/menu-baru',        -- url
    'admin.menu.baru',        -- route_name
    'new_releases',           -- icon (Material Icons name)
    'custom.permission',      -- permission_key
    1,                        -- is_active
    99,                       -- sort_order
    NOW(),
    NOW()
);
```

**Catatan:**
- Icon menggunakan nama dari [Material Icons](https://fonts.google.com/icons)
- Permission key harus sudah ada di tabel `auth_permissions`

---

## Struktur Database

### Tabel: `menus`
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| parent_id | INT | NULL untuk root menu, ID parent untuk submenu |
| title | VARCHAR(100) | Judul menu |
| url | VARCHAR(255) | URL relatif (tanpa base_url) |
| route_name | VARCHAR(100) | Named route (opsional) |
| icon | VARCHAR(50) | Nama icon Material Icons |
| **permission_key** | VARCHAR(100) | Link ke `auth_permissions.name` |
| is_active | TINYINT | 1 = aktif, 0 = nonaktif |
| is_external | TINYINT | 1 = link eksternal, 0 = internal |
| target | ENUM | `_self` atau `_blank` |
| sort_order | INT | Urutan tampilan |
| description | TEXT | Deskripsi menu |
| css_class | VARCHAR(100) | Custom CSS class |
| created_at | DATETIME | Timestamp dibuat |
| updated_at | DATETIME | Timestamp diupdate |
| deleted_at | DATETIME | Soft delete timestamp |

### Relationships
```
auth_groups (roles)
    ↓ (many-to-many)
auth_groups_permissions
    ↓
auth_permissions
    ↓ (referenced by)
menus.permission_key
```

---

## Debugging Tips

### 1. Menu Tidak Muncul

**Kemungkinan Penyebab:**
- Menu tidak aktif (`is_active = 0`)
- User tidak punya permission yang diperlukan
- `permission_key` tidak cocok dengan `auth_permissions.name`
- Menu parent tidak visible (children ikut hidden)

**Cara Check:**
```php
// Di controller atau view
$user = auth()->user();
$permissions = $user->getPermissions(); // Array permission keys yang dimiliki
var_dump($permissions);

// Check specific permission
var_dump($user->can('member.view')); // Should return true/false
```

### 2. Semua Menu Muncul (Tidak Terfilter)

**Kemungkinan Penyebab:**
- `permission_key` di menu adalah NULL (menu publik)
- User adalah superadmin (bypass semua permission check)

**Cara Fix:**
```sql
-- Update menu untuk tambahkan permission_key
UPDATE menus
SET permission_key = 'member.view'
WHERE id = 10;
```

### 3. Error "Call to undefined method can()"

**Penyebab:** User object tidak menggunakan CodeIgniter Shield

**Cara Fix:**
```php
// Pastikan menggunakan auth()->user() bukan $this->request->user()
$user = auth()->user();
```

---

## Best Practices

1. **Selalu gunakan permission key** untuk menu yang sensitif
2. **Menu publik** (tanpa permission_key) akan tampil untuk semua user
3. **Test permission** setelah perubahan di superadmin dashboard
4. **Backup database** sebelum update menu massif
5. **Gunakan soft delete** (`deleted_at`) daripada DELETE permanent
6. **Konsisten naming** untuk permission keys (format: `module.action`)

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Menu tidak muncul setelah assign permission | Clear cache, logout & login ulang |
| Icon tidak muncul | Pastikan Material Icons loaded di layout |
| Submenu tidak expand | Check JavaScript Bootstrap collapse loaded |
| Permission tidak tersimpan | Check foreign key constraint `auth_groups_permissions` |

---

## Kontak Support

Jika ada pertanyaan atau masalah, hubungi SPK Development Team.

**Last Updated:** 2025-01-30
**Version:** 1.0.0
