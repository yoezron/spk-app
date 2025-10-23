<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description">
            <h1><?= esc($title) ?></h1>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted">Total Unit</p>
                        <h4 class="mb-0"><?= $statistics['total_units'] ?? 0 ?></h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-title bg-primary-bright text-primary rounded">
                            <i class="material-icons-outlined">corporate_fare</i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted">Total Posisi</p>
                        <h4 class="mb-0"><?= $statistics['total_positions'] ?? 0 ?></h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-title bg-success-bright text-success rounded">
                            <i class="material-icons-outlined">work</i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted">Posisi Terisi</p>
                        <h4 class="mb-0"><?= $statistics['filled_positions'] ?? 0 ?></h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-title bg-info-bright text-info rounded">
                            <i class="material-icons-outlined">people</i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted">Posisi Kosong</p>
                        <h4 class="mb-0"><?= $statistics['vacant_positions'] ?? 0 ?></h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-title bg-warning-bright text-warning rounded">
                            <i class="material-icons-outlined">assignment_late</i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Filter & Aksi</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('admin/org-structure') ?>" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Scope</label>
                        <select name="scope" class="form-select">
                            <option value="">Semua Scope</option>
                            <option value="pusat" <?= ($filters['scope'] ?? '') === 'pusat' ? 'selected' : '' ?>>Pusat</option>
                            <option value="wilayah" <?= ($filters['scope'] ?? '') === 'wilayah' ? 'selected' : '' ?>>Wilayah</option>
                            <option value="kampus" <?= ($filters['scope'] ?? '') === 'kampus' ? 'selected' : '' ?>>Kampus</option>
                            <option value="departemen" <?= ($filters['scope'] ?? '') === 'departemen' ? 'selected' : '' ?>>Departemen</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Provinsi</label>
                        <select name="region_id" class="form-select">
                            <option value="">Semua Provinsi</option>
                            <?php if (isset($provinces) && is_array($provinces)): ?>
                                <?php foreach ($provinces as $province): ?>
                                    <option value="<?= $province->id ?>" <?= ($filters['region_id'] ?? '') == $province->id ? 'selected' : '' ?>>
                                        <?= esc($province->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= ($filters['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= ($filters['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="material-icons-outlined">filter_list</i> Filter
                        </button>
                        <a href="<?= base_url('admin/org-structure') ?>" class="btn btn-secondary">
                            <i class="material-icons-outlined">refresh</i> Reset
                        </a>
                    </div>
                </form>

                <?php if ($can_manage): ?>
                    <div class="mt-3">
                        <a href="<?= base_url('admin/org-structure/unit/create') ?>" class="btn btn-success">
                            <i class="material-icons-outlined">add</i> Tambah Unit
                        </a>
                        <a href="<?= base_url('admin/org-structure/position/create') ?>" class="btn btn-info">
                            <i class="material-icons-outlined">add_circle</i> Tambah Posisi
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Organization Hierarchy -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Struktur Organisasi</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($hierarchy) && is_array($hierarchy)): ?>
                    <div class="org-hierarchy">
                        <?php foreach ($hierarchy as $unit): ?>
                            <?= $this->include('admin/org_structure/_unit_tree', ['unit' => $unit, 'level' => 0]) ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="material-icons-outlined">info</i>
                        Belum ada struktur organisasi. <?php if ($can_manage): ?>
                            <a href="<?= base_url('admin/org-structure/unit/create') ?>">Tambah unit pertama</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.org-hierarchy {
    font-family: 'Poppins', sans-serif;
}

.unit-item {
    margin-bottom: 20px;
    border-left: 3px solid #4472C4;
    padding-left: 15px;
}

.unit-header {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
}

.unit-header:hover {
    background: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.unit-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.unit-scope {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
}

.positions-list {
    padding-left: 20px;
    margin-top: 10px;
}

.position-item {
    background: #fff;
    border: 1px solid #dee2e6;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.position-title {
    font-weight: 500;
    color: #495057;
}

.position-holder {
    font-size: 13px;
    color: #6c757d;
}

.sub-units {
    margin-top: 15px;
    margin-left: 20px;
}

.badge-scope {
    font-size: 11px;
    padding: 4px 8px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Toggle unit expand/collapse
    $('.unit-header').on('click', function() {
        $(this).next('.positions-list').slideToggle(300);
        $(this).next('.sub-units').slideToggle(300);
    });

    // Delete confirmation
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
<?= $this->endSection() ?>
