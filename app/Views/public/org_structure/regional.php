<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 mb-3">Struktur Regional</h1>
                <p class="lead">Pengurus Serikat Pekerja Kampus di seluruh Indonesia</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?= base_url('org-structure/chart') ?>" class="btn btn-light">
                    <i class="material-icons-outlined">account_tree</i> Struktur Pusat
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- Province Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="<?= current_url() ?>" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="province" class="form-label">Filter Provinsi</label>
                            <select name="province_id" id="province" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Provinsi</option>
                                <?php if (!empty($provinces)): ?>
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?= $province->id ?>"
                                                <?= (isset($_GET['province_id']) && $_GET['province_id'] == $province->id) ? 'selected' : '' ?>>
                                            <?= esc($province->name) ?>
                                            <?php if (isset($province->member_count)): ?>
                                                (<?= number_format($province->member_count) ?> anggota)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="university" class="form-label">Filter Universitas</label>
                            <select name="university_id" id="university" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Universitas</option>
                                <?php if (!empty($universities)): ?>
                                    <?php foreach ($universities as $university): ?>
                                        <option value="<?= $university->id ?>"
                                                <?= (isset($_GET['university_id']) && $_GET['university_id'] == $university->id) ? 'selected' : '' ?>>
                                            <?= esc($university->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <?php if (isset($_GET['province_id']) || isset($_GET['university_id'])): ?>
                                <a href="<?= base_url('org-structure/regional') ?>" class="btn btn-secondary">
                                    <i class="material-icons-outlined">clear</i> Reset Filter
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Regional Overview Map -->
    <?php if (empty($_GET['province_id']) && !empty($regional_overview)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">map</i>
                    Peta Sebaran Regional
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($regional_overview as $region): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="material-icons-outlined text-primary">location_on</i>
                                        <?= esc($region['province_name']) ?>
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Anggota:</small>
                                        <strong><?= number_format($region['member_count']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Universitas:</small>
                                        <strong><?= number_format($region['university_count']) ?></strong>
                                    </div>
                                    <?php if ($region['has_coordinator']): ?>
                                        <span class="badge bg-success">
                                            <i class="material-icons-outlined" style="font-size: 12px;">verified</i>
                                            Ada Koordinator
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum Ada Koordinator</span>
                                    <?php endif; ?>
                                    <a href="?province_id=<?= $region['province_id'] ?>" class="btn btn-sm btn-outline-primary mt-2 w-100">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Regional Coordinators -->
    <?php if (!empty($coordinators)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">supervised_user_circle</i>
                    Koordinator Wilayah
                    <?php if (isset($selected_province)): ?>
                        - <?= esc($selected_province->name) ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($coordinators as $coord): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <?php if (!empty($coord->photo)): ?>
                                        <img src="<?= base_url('uploads/profiles/' . $coord->photo) ?>"
                                             alt="<?= esc($coord->name) ?>"
                                             class="rounded-circle mb-3"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar bg-primary-bright text-primary rounded-circle mx-auto mb-3"
                                             style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                            <i class="material-icons-outlined" style="font-size: 50px;">person</i>
                                        </div>
                                    <?php endif; ?>

                                    <h6 class="mb-1"><?= esc($coord->name) ?></h6>
                                    <p class="text-primary small mb-1">
                                        <strong><?= esc($coord->position_name) ?></strong>
                                    </p>
                                    <p class="text-muted small mb-3">
                                        <i class="material-icons-outlined" style="font-size: 14px;">location_on</i>
                                        <?= esc($coord->province_name) ?>
                                    </p>

                                    <div class="d-flex gap-2 justify-content-center">
                                        <?php if (!empty($coord->email)): ?>
                                            <a href="mailto:<?= esc($coord->email) ?>"
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip"
                                               title="Email">
                                                <i class="material-icons-outlined" style="font-size: 16px;">email</i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('org-structure/detail/' . $coord->id) ?>"
                                           class="btn btn-sm btn-primary">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Regional Members by University -->
    <?php if (!empty($members_by_university)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="material-icons-outlined">school</i>
                    Anggota per Universitas
                    <?php if (isset($selected_province)): ?>
                        di <?= esc($selected_province->name) ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="universityAccordion">
                    <?php $index = 0; ?>
                    <?php foreach ($members_by_university as $univName => $members): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?= $index ?>"
                                        aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                                        aria-controls="collapse<?= $index ?>">
                                    <strong><?= esc($univName) ?></strong>
                                    <span class="badge bg-primary ms-2"><?= count($members) ?> anggota</span>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>"
                                 class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                 aria-labelledby="heading<?= $index ?>"
                                 data-bs-parent="#universityAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <?php foreach ($members as $member): ?>
                                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body text-center p-3">
                                                        <?php if (!empty($member->photo)): ?>
                                                            <img src="<?= base_url('uploads/profiles/' . $member->photo) ?>"
                                                                 alt="<?= esc($member->name) ?>"
                                                                 class="rounded-circle mb-2"
                                                                 style="width: 70px; height: 70px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="avatar bg-secondary-bright text-secondary rounded-circle mx-auto mb-2"
                                                                 style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="material-icons-outlined" style="font-size: 35px;">person</i>
                                                            </div>
                                                        <?php endif; ?>

                                                        <h6 class="mb-1 small"><?= esc($member->name) ?></h6>
                                                        <?php if (!empty($member->position_name)): ?>
                                                            <p class="text-muted small mb-2"><?= esc($member->position_name) ?></p>
                                                        <?php endif; ?>

                                                        <a href="<?= base_url('org-structure/detail/' . $member->id) ?>"
                                                           class="btn btn-sm btn-outline-primary">
                                                            Detail
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Empty State -->
    <?php if (empty($coordinators) && empty($members_by_university) && empty($regional_overview)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="material-icons-outlined text-muted" style="font-size: 64px;">map</i>
                <h4 class="mt-3 text-muted">Belum Ada Data Regional</h4>
                <p class="text-muted">
                    <?php if (isset($_GET['province_id']) || isset($_GET['university_id'])): ?>
                        Tidak ada data untuk filter yang dipilih. Silakan pilih filter lain.
                    <?php else: ?>
                        Informasi struktur regional akan segera ditampilkan.
                    <?php endif; ?>
                </p>
                <?php if (isset($_GET['province_id']) || isset($_GET['university_id'])): ?>
                    <a href="<?= base_url('org-structure/regional') ?>" class="btn btn-primary mt-3">
                        Lihat Semua Regional
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Join CTA -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center py-5">
                    <h3 class="mb-3">Belum Ada Koordinator di Wilayah Anda?</h3>
                    <p class="lead mb-4">
                        Kami mencari individu yang berdedikasi untuk menjadi koordinator wilayah.
                        Bantu kami memperkuat organisasi di daerah Anda!
                    </p>
                    <div class="btn-group" role="group">
                        <a href="<?= base_url('contact') ?>" class="btn btn-primary btn-lg">
                            <i class="material-icons-outlined">mail</i>
                            Hubungi Kami
                        </a>
                        <a href="<?= base_url('about') ?>" class="btn btn-outline-primary btn-lg">
                            <i class="material-icons-outlined">info</i>
                            Tentang SPK
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
<?= $this->endSection() ?>
