<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="row">
    <div class="col">
        <div class="page-description d-flex justify-content-between align-items-center">
            <div>
                <h1><?= esc($title) ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/org-structure') ?>">Struktur Organisasi</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= esc($unit['name'] ?? 'Detail Unit') ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="<?= base_url('admin/org-structure') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <?php if ($can_manage): ?>
                    <a href="<?= base_url('admin/org-structure/unit/' . ($unit['id'] ?? 0) . '/edit') ?>" class="btn btn-primary">
                        <i class="material-icons-outlined">edit</i> Edit Unit
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Unit Information -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Informasi Unit</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">Nama Unit</th>
                            <td><?= esc($unit['name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Scope</th>
                            <td>
                                <span class="badge bg-primary"><?= esc(ucfirst($unit['scope'] ?? 'N/A')) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Level</th>
                            <td><?= esc($unit['level'] ?? 'N/A') ?></td>
                        </tr>
                        <?php if (!empty($unit['parent_name'])): ?>
                            <tr>
                                <th>Unit Induk</th>
                                <td>
                                    <a href="<?= base_url('admin/org-structure/unit/' . ($unit['parent_id'] ?? 0)) ?>">
                                        <?= esc($unit['parent_name']) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($unit['province_name'])): ?>
                            <tr>
                                <th>Provinsi</th>
                                <td><?= esc($unit['province_name']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($unit['university_name'])): ?>
                            <tr>
                                <th>Universitas</th>
                                <td><?= esc($unit['university_name']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($unit['description'])): ?>
                            <tr>
                                <th>Deskripsi</th>
                                <td><?= nl2br(esc($unit['description'])) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($unit['is_active'] ?? false): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td><?= !empty($unit['created_at']) ? date('d/m/Y H:i', strtotime($unit['created_at'])) : 'N/A' ?></td>
                        </tr>
                        <?php if (!empty($unit['updated_at'])): ?>
                            <tr>
                                <th>Terakhir Diubah</th>
                                <td><?= date('d/m/Y H:i', strtotime($unit['updated_at'])) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Statistik</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Posisi</span>
                        <strong><?= count($unit['positions'] ?? []) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Posisi Terisi</span>
                        <strong class="text-success">
                            <?php
                            $filled = 0;
                            if (!empty($unit['positions'])) {
                                foreach ($unit['positions'] as $pos) {
                                    if (!empty($pos['current_holders'])) {
                                        $filled++;
                                    }
                                }
                            }
                            echo $filled;
                            ?>
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Posisi Kosong</span>
                        <strong class="text-warning">
                            <?= count($unit['positions'] ?? []) - $filled ?>
                        </strong>
                    </div>
                </div>

                <?php if ($can_manage): ?>
                    <hr>
                    <a href="<?= base_url('admin/org-structure/position/create?unit_id=' . ($unit['id'] ?? 0)) ?>"
                       class="btn btn-primary w-100">
                        <i class="material-icons-outlined">add</i> Tambah Posisi
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Positions List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Posisi/Jabatan</h5>
                <?php if ($can_manage): ?>
                    <a href="<?= base_url('admin/org-structure/position/create?unit_id=' . ($unit['id'] ?? 0)) ?>"
                       class="btn btn-sm btn-primary">
                        <i class="material-icons-outlined">add</i> Tambah Posisi
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($unit['positions']) && is_array($unit['positions'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Posisi</th>
                                    <th>Tipe</th>
                                    <th>Level</th>
                                    <th>Pemegang Jabatan</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unit['positions'] as $position): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($position['title'] ?? 'N/A') ?></strong>
                                            <?php if (!empty($position['description'])): ?>
                                                <br><small class="text-muted"><?= esc($position['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= esc(ucfirst($position['position_type'] ?? 'N/A')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= esc(ucfirst($position['position_level'] ?? 'N/A')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($position['current_holders']) && is_array($position['current_holders'])): ?>
                                                <?php foreach ($position['current_holders'] as $holder): ?>
                                                    <div class="mb-1">
                                                        <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">person</i>
                                                        <?= esc($holder['full_name'] ?? $holder['email'] ?? 'N/A') ?>
                                                        <?php if (!empty($holder['started_at'])): ?>
                                                            <br><small class="text-muted">Sejak: <?= date('d/m/Y', strtotime($holder['started_at'])) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-warning">
                                                    <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">warning</i>
                                                    Kosong
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($position['is_active'] ?? false): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($can_assign): ?>
                                                <a href="<?= base_url('admin/org-structure/position/' . ($position['id'] ?? 0) . '/assign') ?>"
                                                   class="btn btn-sm btn-success" title="Assign Anggota">
                                                    <i class="material-icons-outlined">person_add</i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($can_manage): ?>
                                                <a href="<?= base_url('admin/org-structure/position/' . ($position['id'] ?? 0) . '/edit') ?>"
                                                   class="btn btn-sm btn-info" title="Edit">
                                                    <i class="material-icons-outlined">edit</i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-position"
                                                        data-position-id="<?= $position['id'] ?? 0 ?>" title="Hapus">
                                                    <i class="material-icons-outlined">delete</i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="material-icons-outlined">info</i>
                        Belum ada posisi di unit ini.
                        <?php if ($can_manage): ?>
                            <a href="<?= base_url('admin/org-structure/position/create?unit_id=' . ($unit['id'] ?? 0)) ?>">
                                Tambah posisi pertama
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Sub Units -->
<?php if (!empty($unit['children']) && is_array($unit['children'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Sub Unit</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($unit['children'] as $child): ?>
                            <a href="<?= base_url('admin/org-structure/unit/' . ($child['id'] ?? 0)) ?>"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="material-icons-outlined">corporate_fare</i>
                                        <strong><?= esc($child['name'] ?? 'N/A') ?></strong>
                                        <span class="badge bg-secondary ms-2"><?= esc(strtoupper($child['scope'] ?? '')) ?></span>
                                    </div>
                                    <i class="material-icons-outlined">chevron_right</i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Delete position confirmation
    $('.btn-delete-position').on('click', function(e) {
        e.preventDefault();
        const positionId = $(this).data('position-id');

        if (confirm('Apakah Anda yakin ingin menghapus posisi ini?')) {
            $.ajax({
                url: '<?= base_url('admin/org-structure/position/') ?>' + positionId + '/delete',
                type: 'DELETE',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Posisi berhasil dihapus');
                        location.reload();
                    } else {
                        alert(response.message || 'Gagal menghapus posisi');
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus posisi');
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
