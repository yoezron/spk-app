<?= $this->extend('layouts/super') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Preview Menu</h1>
            <p class="text-muted">Preview struktur menu sistem</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('super/menus') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3">
        <!-- Menu Preview Sidebar -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">
                    <i class="material-icons-outlined">menu</i>
                    Menu Preview
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (isset($menuItems) && !empty($menuItems)): ?>
                        <?php foreach ($menuItems as $item): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($item->icon)): ?>
                                        <i class="material-icons-outlined me-2"><?= esc($item->icon) ?></i>
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <strong><?= esc($item->title) ?></strong>
                                        <?php if (!empty($item->url)): ?>
                                            <br><small class="text-muted"><?= esc($item->url) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (isset($item->children) && !empty($item->children)): ?>
                                        <span class="badge bg-primary"><?= count($item->children) ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Sub-menu items -->
                                <?php if (isset($item->children) && !empty($item->children)): ?>
                                    <div class="list-group list-group-flush mt-2 ms-4">
                                        <?php foreach ($item->children as $child): ?>
                                            <div class="list-group-item border-0 py-2">
                                                <small>
                                                    <?php if (!empty($child->icon)): ?>
                                                        <i class="material-icons-outlined" style="font-size: 14px;"><?= esc($child->icon) ?></i>
                                                    <?php endif; ?>
                                                    <?= esc($child->title) ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="material-icons-outlined" style="font-size: 48px;">menu_open</i>
                            <p class="mt-2 mb-0">Tidak ada menu untuk ditampilkan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- Menu Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">list_alt</i>
                    Detail Menu Struktur
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($menuItems) && !empty($menuItems)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Icon</th>
                                    <th>Judul</th>
                                    <th>URL</th>
                                    <th>Parent</th>
                                    <th>Urutan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                function displayMenuRow($item, $level = 0, $parent = null) {
                                    ?>
                                    <tr>
                                        <td>
                                            <?= str_repeat('—', $level) ?>
                                            Level <?= $level ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item->icon)): ?>
                                                <i class="material-icons-outlined"><?= esc($item->icon) ?></i>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= esc($item->title) ?></strong>
                                        </td>
                                        <td>
                                            <code><?= esc($item->url ?? '-') ?></code>
                                        </td>
                                        <td>
                                            <?= $parent ? esc($parent) : '<span class="text-muted">Root</span>' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= $item->order ?? 0 ?></span>
                                        </td>
                                        <td>
                                            <?php if ($item->is_active ?? 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    if (isset($item->children) && !empty($item->children)) {
                                        foreach ($item->children as $child) {
                                            displayMenuRow($child, $level + 1, $item->title);
                                        }
                                    }
                                }

                                foreach ($menuItems as $item) {
                                    displayMenuRow($item);
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="material-icons-outlined" style="font-size: 64px;">menu_open</i>
                        <p class="mt-3 mb-0">Belum ada menu yang dikonfigurasi</p>
                        <a href="<?= base_url('super/menus/create') ?>" class="btn btn-primary mt-3">
                            <i class="material-icons-outlined">add</i>
                            Tambah Menu
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu Statistics -->
        <?php if (isset($menuItems) && !empty($menuItems)): ?>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Total Menu Items</h6>
                            <h3><?= isset($stats['total']) ? $stats['total'] : count($menuItems) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Menu Aktif</h6>
                            <h3 class="text-success"><?= $stats['active'] ?? 0 ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">Max Level</h6>
                            <h3><?= $stats['max_level'] ?? 1 ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
