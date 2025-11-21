<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 mb-3">Tim Kepemimpinan</h1>
                <p class="lead">Pengurus Serikat Pekerja Kampus Indonesia Periode <?= date('Y') ?></p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?= base_url('org-structure/chart') ?>" class="btn btn-light">
                    <i class="material-icons-outlined">account_tree</i> Lihat Struktur
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- Executive Board -->
    <?php if (!empty($executive_board)): ?>
        <div class="mb-5">
            <div class="text-center mb-4">
                <h2 class="mb-2">Dewan Pengurus Pusat</h2>
                <p class="text-muted">Pimpinan tertinggi organisasi</p>
            </div>

            <div class="row justify-content-center">
                <?php foreach ($executive_board as $leader): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <?php if (!empty($leader->photo)): ?>
                                    <img src="<?= base_url('uploads/profiles/' . $leader->photo) ?>"
                                         alt="<?= esc($leader->name) ?>"
                                         class="rounded-circle mb-3"
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar bg-primary-bright text-primary rounded-circle mx-auto mb-3"
                                         style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                                        <i class="material-icons-outlined" style="font-size: 60px;">person</i>
                                    </div>
                                <?php endif; ?>

                                <h5 class="mb-1"><?= esc($leader->name) ?></h5>
                                <p class="text-primary mb-3">
                                    <strong><?= esc($leader->position_name) ?></strong>
                                </p>

                                <?php if (!empty($leader->short_bio)): ?>
                                    <p class="text-muted small mb-3">
                                        <?= esc(mb_substr($leader->short_bio, 0, 120)) ?>...
                                    </p>
                                <?php endif; ?>

                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (!empty($leader->email)): ?>
                                        <a href="mailto:<?= esc($leader->email) ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Email">
                                            <i class="material-icons-outlined" style="font-size: 16px;">email</i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($leader->phone)): ?>
                                        <a href="tel:<?= esc($leader->phone) ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip"
                                           title="Phone">
                                            <i class="material-icons-outlined" style="font-size: 16px;">phone</i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('org-structure/detail/' . $leader->id) ?>"
                                       class="btn btn-sm btn-primary">
                                        <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Department Heads -->
    <?php if (!empty($departments)): ?>
        <?php foreach ($departments as $deptName => $deptMembers): ?>
            <div class="mb-5">
                <div class="mb-4">
                    <h3 class="mb-2">
                        <i class="material-icons-outlined">business</i>
                        <?= esc($deptName) ?>
                    </h3>
                    <hr>
                </div>

                <div class="row">
                    <?php foreach ($deptMembers as $member): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <?php if (!empty($member->photo)): ?>
                                        <img src="<?= base_url('uploads/profiles/' . $member->photo) ?>"
                                             alt="<?= esc($member->name) ?>"
                                             class="rounded-circle mb-3"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="avatar bg-secondary-bright text-secondary rounded-circle mx-auto mb-3"
                                             style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                            <i class="material-icons-outlined" style="font-size: 50px;">person</i>
                                        </div>
                                    <?php endif; ?>

                                    <h6 class="mb-1"><?= esc($member->name) ?></h6>
                                    <p class="text-muted small mb-3"><?= esc($member->position_name) ?></p>

                                    <a href="<?= base_url('org-structure/detail/' . $member->id) ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Regional Coordinators -->
    <?php if (!empty($regional_coordinators)): ?>
        <div class="mb-5">
            <div class="mb-4">
                <h3 class="mb-2">
                    <i class="material-icons-outlined">map</i>
                    Koordinator Wilayah
                </h3>
                <hr>
            </div>

            <div class="row">
                <?php foreach ($regional_coordinators as $coord): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <?php if (!empty($coord->photo)): ?>
                                    <img src="<?= base_url('uploads/profiles/' . $coord->photo) ?>"
                                         alt="<?= esc($coord->name) ?>"
                                         class="rounded-circle mb-3"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar bg-info-bright text-info rounded-circle mx-auto mb-3"
                                         style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                                        <i class="material-icons-outlined" style="font-size: 50px;">person</i>
                                    </div>
                                <?php endif; ?>

                                <h6 class="mb-1"><?= esc($coord->name) ?></h6>
                                <p class="text-muted small mb-1"><?= esc($coord->position_name) ?></p>
                                <p class="text-primary small mb-3">
                                    <i class="material-icons-outlined" style="font-size: 14px;">location_on</i>
                                    <?= esc($coord->province_name) ?>
                                </p>

                                <a href="<?= base_url('org-structure/detail/' . $coord->id) ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Empty State -->
    <?php if (empty($executive_board) && empty($departments) && empty($regional_coordinators)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="material-icons-outlined text-muted" style="font-size: 64px;">people_outline</i>
                <h4 class="mt-3 text-muted">Belum Ada Data Kepemimpinan</h4>
                <p class="text-muted">Informasi tim kepemimpinan akan segera ditampilkan.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Call to Action -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center py-5">
                    <h3 class="mb-3">Ingin Bergabung dengan Tim Kami?</h3>
                    <p class="lead mb-4">
                        Kami selalu mencari individu yang berdedikasi untuk membantu memperjuangkan
                        hak dan kesejahteraan pekerja kampus di Indonesia.
                    </p>
                    <div class="btn-group" role="group">
                        <a href="<?= base_url('register') ?>" class="btn btn-primary btn-lg">
                            <i class="material-icons-outlined">person_add</i>
                            Daftar Sebagai Anggota
                        </a>
                        <a href="<?= base_url('contact') ?>" class="btn btn-outline-primary btn-lg">
                            <i class="material-icons-outlined">mail</i>
                            Hubungi Kami
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
