<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $title ?></h1>
            <p class="text-muted">Kelola kategori forum untuk mengorganisir diskusi</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('admin/forum') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="material-icons-outlined">add</i> Tambah Kategori
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Categories List -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Daftar Kategori</h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary"><?= count($categories) ?> Kategori</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($categories)): ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">category</i>
                <p class="mt-3 mb-0">Belum ada kategori</p>
                <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="material-icons-outlined">add</i> Tambah Kategori Pertama
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Urutan</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Slug</th>
                            <th class="text-center">Jumlah Thread</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTable">
                        <?php foreach ($categories as $category): ?>
                            <tr data-category-id="<?= $category->id ?>">
                                <td class="text-center">
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm move-up" title="Naikkan">
                                            <i class="material-icons-outlined" style="font-size: 12px;">arrow_upward</i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm move-down" title="Turunkan">
                                            <i class="material-icons-outlined" style="font-size: 12px;">arrow_downward</i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($category->icon)): ?>
                                            <i class="material-icons-outlined me-2" style="color: <?= $category->color ?? '#666' ?>;">
                                                <?= esc($category->icon) ?>
                                            </i>
                                        <?php endif; ?>
                                        <strong><?= esc($category->name) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= esc($category->description) ?: '-' ?>
                                    </small>
                                </td>
                                <td>
                                    <code><?= esc($category->slug) ?></code>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= number_format($category->thread_count ?? 0) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($category->is_active ?? 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Edit -->
                                        <button type="button" class="btn btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal<?= $category->id ?>">
                                            <i class="material-icons-outlined" style="font-size: 16px;">edit</i>
                                        </button>

                                        <!-- Delete -->
                                        <?php if ($category->thread_count == 0): ?>
                                            <form action="<?= base_url('admin/forum/category/delete/' . $category->id) ?>"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Hapus kategori ini?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="material-icons-outlined" style="font-size: 16px;">delete</i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled
                                                    title="Kategori dengan thread tidak dapat dihapus">
                                                <i class="material-icons-outlined" style="font-size: 16px;">delete</i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Category Modal -->
                            <div class="modal fade" id="editCategoryModal<?= $category->id ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="<?= base_url('admin/forum/category/update/' . $category->id) ?>" method="POST">
                                            <?= csrf_field() ?>
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Kategori</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                           value="<?= esc($category->name) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description" class="form-label">Deskripsi</label>
                                                    <textarea class="form-control" name="description" rows="3"><?= esc($category->description) ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="slug"
                                                           value="<?= esc($category->slug) ?>" required>
                                                    <small class="text-muted">URL-friendly name (gunakan huruf kecil dan tanda hubung)</small>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="icon" class="form-label">Icon (Material Icons)</label>
                                                            <input type="text" class="form-control" name="icon"
                                                                   value="<?= esc($category->icon ?? '') ?>"
                                                                   placeholder="forum">
                                                            <small class="text-muted">
                                                                <a href="https://fonts.google.com/icons" target="_blank">Browse icons</a>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label for="color" class="form-label">Warna</label>
                                                            <input type="color" class="form-control form-control-color" name="color"
                                                                   value="<?= esc($category->color ?? '#0d6efd') ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                               id="is_active_edit_<?= $category->id ?>"
                                                               <?= ($category->is_active ?? 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="is_active_edit_<?= $category->id ?>">
                                                            Kategori Aktif
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/forum/category/create') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="slug" id="slug" required>
                        <small class="text-muted">URL-friendly name (gunakan huruf kecil dan tanda hubung)</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="icon" class="form-label">Icon (Material Icons)</label>
                                <input type="text" class="form-control" name="icon" id="icon" placeholder="forum">
                                <small class="text-muted">
                                    <a href="https://fonts.google.com/icons" target="_blank">Browse icons</a>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label">Warna</label>
                                <input type="color" class="form-control form-control-color" name="color" id="color" value="#0d6efd">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="is_active_create" checked>
                            <label class="form-check-label" for="is_active_create">
                                Kategori Aktif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });

    // Category ordering (move up/down)
    document.querySelectorAll('.move-up').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const prev = row.previousElementSibling;
            if (prev && prev.tagName === 'TR') {
                row.parentNode.insertBefore(row, prev);
                updateCategoryOrder();
            }
        });
    });

    document.querySelectorAll('.move-down').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const next = row.nextElementSibling;
            if (next && next.tagName === 'TR') {
                row.parentNode.insertBefore(next, row);
                updateCategoryOrder();
            }
        });
    });

    function updateCategoryOrder() {
        const rows = document.querySelectorAll('#categoriesTable tr');
        const order = [];

        rows.forEach((row, index) => {
            const categoryId = row.dataset.categoryId;
            if (categoryId) {
                order.push({
                    id: categoryId,
                    order: index + 1
                });
            }
        });

        // Send AJAX request to update order
        fetch('<?= base_url('admin/forum/category/update-order') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            },
            body: JSON.stringify({ order: order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Order updated successfully');
            }
        })
        .catch(error => {
            console.error('Error updating order:', error);
        });
    }
</script>
<?= $this->endSection() ?>
