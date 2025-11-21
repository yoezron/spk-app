<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">Detail Pengurus</h1>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('org-structure/chart') ?>" class="btn btn-light">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <?php if (!empty($member)): ?>
        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if (!empty($member->photo)): ?>
                            <img src="<?= base_url('uploads/profiles/' . $member->photo) ?>"
                                 alt="<?= esc($member->name) ?>"
                                 class="rounded-circle mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="avatar bg-primary-bright text-primary rounded-circle mx-auto mb-3"
                                 style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
                                <i class="material-icons-outlined" style="font-size: 80px;">person</i>
                            </div>
                        <?php endif; ?>

                        <h4 class="mb-1"><?= esc($member->name) ?></h4>
                        <p class="text-primary mb-3">
                            <strong><?= esc($member->position_name) ?></strong>
                        </p>

                        <?php if (!empty($member->department)): ?>
                            <p class="text-muted mb-3">
                                <i class="material-icons-outlined" style="font-size: 18px;">business</i>
                                <?= esc($member->department) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($member->email)): ?>
                            <a href="mailto:<?= esc($member->email) ?>" class="btn btn-outline-primary btn-sm mb-2 w-100">
                                <i class="material-icons-outlined">email</i>
                                <?= esc($member->email) ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($member->phone)): ?>
                            <a href="tel:<?= esc($member->phone) ?>" class="btn btn-outline-primary btn-sm w-100">
                                <i class="material-icons-outlined">phone</i>
                                <?= esc($member->phone) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <?php if (!empty($stats)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="material-icons-outlined">analytics</i>
                                Statistik
                            </h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (isset($stats['subordinates'])): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Bawahan Langsung</span>
                                    <span class="badge bg-primary rounded-pill"><?= number_format($stats['subordinates']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($stats['team_size'])): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Total Tim</span>
                                    <span class="badge bg-info rounded-pill"><?= number_format($stats['team_size']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($stats['years_of_service'])): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Masa Jabatan</span>
                                    <span class="badge bg-success rounded-pill"><?= number_format($stats['years_of_service'], 1) ?> tahun</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details -->
            <div class="col-lg-8">
                <!-- Position Information -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="material-icons-outlined">badge</i>
                            Informasi Jabatan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Posisi</label>
                                <p class="mb-0"><strong><?= esc($member->position_name) ?></strong></p>
                            </div>
                            <?php if (!empty($member->department)): ?>
                                <div class="col-md-6">
                                    <label class="text-muted small">Departemen</label>
                                    <p class="mb-0"><?= esc($member->department) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($member->parent_name)): ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="text-muted small">Melapor Kepada</label>
                                    <p class="mb-0">
                                        <a href="<?= base_url('org-structure/detail/' . $member->parent_id) ?>">
                                            <?= esc($member->parent_name) ?>
                                        </a>
                                        <small class="text-muted">(<?= esc($member->parent_position) ?>)</small>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($member->start_date)): ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Mulai Menjabat</label>
                                    <p class="mb-0"><?= date('d F Y', strtotime($member->start_date)) ?></p>
                                </div>
                                <?php if (!empty($member->end_date)): ?>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Berakhir</label>
                                        <p class="mb-0"><?= date('d F Y', strtotime($member->end_date)) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($member->responsibilities)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <label class="text-muted small">Tanggung Jawab</label>
                                    <div><?= nl2br(esc($member->responsibilities)) ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Biography -->
                <?php if (!empty($member->biography)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="material-icons-outlined">person_outline</i>
                                Biografi
                            </h5>
                        </div>
                        <div class="card-body">
                            <?= nl2br(esc($member->biography)) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Education & Experience -->
                <div class="row">
                    <?php if (!empty($member->education)): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="material-icons-outlined">school</i>
                                        Pendidikan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?= nl2br(esc($member->education)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($member->experience)): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="material-icons-outlined">work</i>
                                        Pengalaman
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?= nl2br(esc($member->experience)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Subordinates -->
                <?php if (!empty($subordinates)): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="material-icons-outlined">groups</i>
                                Tim dan Bawahan (<?= count($subordinates) ?>)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($subordinates as $sub): ?>
                                    <a href="<?= base_url('org-structure/detail/' . $sub->id) ?>"
                                       class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($sub->photo)): ?>
                                                <img src="<?= base_url('uploads/profiles/' . $sub->photo) ?>"
                                                     alt="<?= esc($sub->name) ?>"
                                                     class="rounded-circle me-3"
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="avatar bg-secondary-bright text-secondary rounded-circle me-3"
                                                     style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="material-icons-outlined">person</i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?= esc($sub->name) ?></h6>
                                                <small class="text-muted"><?= esc($sub->position_name) ?></small>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Location Information -->
                <?php if (!empty($member->province_name) || !empty($member->university_name)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="material-icons-outlined">location_on</i>
                                Informasi Lokasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (!empty($member->province_name)): ?>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Provinsi</label>
                                        <p class="mb-0"><?= esc($member->province_name) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($member->university_name)): ?>
                                    <div class="col-md-6">
                                        <label class="text-muted small">Universitas</label>
                                        <p class="mb-0"><?= esc($member->university_name) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="material-icons-outlined text-muted" style="font-size: 64px;">person_off</i>
                <h4 class="mt-3 text-muted">Data Tidak Ditemukan</h4>
                <p class="text-muted">Pengurus yang Anda cari tidak ditemukan atau sudah tidak aktif.</p>
                <a href="<?= base_url('org-structure/chart') ?>" class="btn btn-primary mt-3">
                    Kembali ke Struktur Organisasi
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
