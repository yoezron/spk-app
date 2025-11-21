<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 mb-3">Struktur Organisasi</h1>
                <p class="lead">Bagan organisasi Serikat Pekerja Kampus Indonesia</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- View Options -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                <i class="material-icons-outlined">account_tree</i>
                                Bagan Organisasi
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary active" id="viewTree">
                                    <i class="material-icons-outlined">account_tree</i> Tree View
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="viewList">
                                    <i class="material-icons-outlined">list</i> List View
                                </button>
                            </div>
                            <a href="<?= base_url('org-structure/leadership') ?>" class="btn btn-primary ms-2">
                                <i class="material-icons-outlined">people</i> Tim Kepemimpinan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tree View -->
    <div id="treeViewSection">
        <div class="card mb-4">
            <div class="card-body">
                <div class="org-chart-container">
                    <?php if (!empty($orgStructure)): ?>
                        <div class="org-tree">
                            <?php
                            function renderOrgNode($node, $level = 0) {
                                ?>
                                <div class="org-node level-<?= $level ?>">
                                    <div class="org-card">
                                        <div class="org-card-header">
                                            <?php if (!empty($node['photo'])): ?>
                                                <img src="<?= base_url('uploads/profiles/' . $node['photo']) ?>"
                                                     alt="<?= esc($node['name']) ?>"
                                                     class="org-photo">
                                            <?php else: ?>
                                                <div class="org-photo-placeholder">
                                                    <i class="material-icons-outlined">person</i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="org-card-body">
                                            <h6 class="org-name"><?= esc($node['name']) ?></h6>
                                            <p class="org-position"><?= esc($node['position_name']) ?></p>
                                            <?php if (!empty($node['department'])): ?>
                                                <p class="org-department text-muted">
                                                    <small><?= esc($node['department']) ?></small>
                                                </p>
                                            <?php endif; ?>
                                            <a href="<?= base_url('org-structure/detail/' . $node['id']) ?>"
                                               class="btn btn-sm btn-outline-primary mt-2">
                                                Lihat Detail
                                            </a>
                                        </div>
                                    </div>

                                    <?php if (!empty($node['children'])): ?>
                                        <div class="org-children">
                                            <?php foreach ($node['children'] as $child): ?>
                                                <?php renderOrgNode($child, $level + 1); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                            }

                            // Render root node(s)
                            foreach ($orgStructure as $rootNode) {
                                renderOrgNode($rootNode);
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="material-icons-outlined" style="font-size: 64px;">account_tree</i>
                            <p class="mt-3 mb-0">Struktur organisasi belum tersedia</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="listViewSection" style="display: none;">
        <div class="card">
            <div class="card-body p-0">
                <?php if (!empty($orgList)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($orgList as $item): ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <?php if (!empty($item->photo)): ?>
                                            <img src="<?= base_url('uploads/profiles/' . $item->photo) ?>"
                                                 alt="<?= esc($item->name) ?>"
                                                 class="rounded-circle"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="avatar bg-primary-bright text-primary rounded-circle"
                                                 style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                <i class="material-icons-outlined">person</i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <h6 class="mb-1"><?= esc($item->name) ?></h6>
                                        <p class="text-primary mb-1">
                                            <strong><?= esc($item->position_name) ?></strong>
                                        </p>
                                        <?php if (!empty($item->department)): ?>
                                            <p class="text-muted small mb-0">
                                                <i class="material-icons-outlined" style="font-size: 14px;">business</i>
                                                <?= esc($item->department) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($item->parent_name)): ?>
                                            <p class="text-muted small mb-0">
                                                <i class="material-icons-outlined" style="font-size: 14px;">supervisor_account</i>
                                                Melapor ke: <?= esc($item->parent_name) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-auto">
                                        <a href="<?= base_url('org-structure/detail/' . $item->id) ?>"
                                           class="btn btn-outline-primary">
                                            <i class="material-icons-outlined">visibility</i> Lihat Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <i class="material-icons-outlined" style="font-size: 64px;">list</i>
                        <p class="mt-3 mb-0">Tidak ada data struktur organisasi</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="material-icons-outlined text-primary" style="font-size: 48px;">people</i>
                    <h5 class="card-title mt-3">Tim Kepemimpinan</h5>
                    <p class="card-text">Lihat profil lengkap tim kepemimpinan SPK</p>
                    <a href="<?= base_url('org-structure/leadership') ?>" class="btn btn-primary">
                        Lihat Tim
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="material-icons-outlined text-primary" style="font-size: 48px;">map</i>
                    <h5 class="card-title mt-3">Struktur Regional</h5>
                    <p class="card-text">Lihat struktur organisasi per wilayah</p>
                    <a href="<?= base_url('org-structure/regional') ?>" class="btn btn-primary">
                        Lihat Regional
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="material-icons-outlined text-primary" style="font-size: 48px;">contact_mail</i>
                    <h5 class="card-title mt-3">Hubungi Pengurus</h5>
                    <p class="card-text">Ada pertanyaan? Hubungi tim kami</p>
                    <a href="<?= base_url('contact') ?>" class="btn btn-primary">
                        Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .org-chart-container {
        overflow-x: auto;
        padding: 20px;
    }

    .org-tree {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .org-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: 10px;
    }

    .org-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        min-width: 200px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .org-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }

    .org-photo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
    }

    .org-photo-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
    }

    .org-name {
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }

    .org-position {
        color: #007bff;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .org-department {
        font-size: 12px;
        margin-bottom: 0;
    }

    .org-children {
        display: flex;
        flex-direction: row;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
        position: relative;
    }

    .org-node.level-0 > .org-card {
        border-color: #007bff;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .org-node.level-0 .org-name,
    .org-node.level-0 .org-position,
    .org-node.level-0 .org-department {
        color: white;
    }

    @media (max-width: 768px) {
        .org-children {
            flex-direction: column;
        }

        .org-card {
            min-width: 180px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewTreeBtn = document.getElementById('viewTree');
        const viewListBtn = document.getElementById('viewList');
        const treeSection = document.getElementById('treeViewSection');
        const listSection = document.getElementById('listViewSection');

        viewTreeBtn.addEventListener('click', function() {
            treeSection.style.display = 'block';
            listSection.style.display = 'none';
            viewTreeBtn.classList.add('active');
            viewListBtn.classList.remove('active');
        });

        viewListBtn.addEventListener('click', function() {
            treeSection.style.display = 'none';
            listSection.style.display = 'block';
            viewListBtn.classList.add('active');
            viewTreeBtn.classList.remove('active');
        });
    });
</script>
<?= $this->endSection() ?>
