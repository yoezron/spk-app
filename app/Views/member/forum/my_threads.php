<?= $this->extend('layouts/member') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Thread Saya</h1>
            <p class="text-muted">Kelola semua thread diskusi yang Anda buat</p>
        </div>
        <div class="col-auto">
            <div class="btn-group" role="group">
                <a href="<?= base_url('member/forum') ?>" class="btn btn-secondary">
                    <i class="material-icons-outlined">arrow_back</i> Kembali
                </a>
                <a href="<?= base_url('member/forum/thread/create') ?>" class="btn btn-primary">
                    <i class="material-icons-outlined">add</i> Buat Thread Baru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-3">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Thread</h6>
                        <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-primary-bright text-primary rounded">
                        <i class="material-icons-outlined">forum</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Balasan</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_replies'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-info-bright text-info rounded">
                        <i class="material-icons-outlined">chat</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Views</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_views'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-success-bright text-success rounded">
                        <i class="material-icons-outlined">visibility</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Thread Aktif</h6>
                        <h3 class="mb-0"><?= number_format($stats['active'] ?? 0) ?></h3>
                    </div>
                    <div class="avatar bg-warning-bright text-warning rounded">
                        <i class="material-icons-outlined">trending_up</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= !isset($_GET['status']) || $_GET['status'] == '' ? 'active' : '' ?>"
           href="<?= base_url('member/forum/my-threads') ?>">
            Semua Thread
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] == 'pinned' ? 'active' : '' ?>"
           href="<?= base_url('member/forum/my-threads?status=pinned') ?>">
            <i class="material-icons-outlined" style="font-size: 14px;">push_pin</i>
            Disematkan
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= isset($_GET['status']) && $_GET['status'] == 'locked' ? 'active' : '' ?>"
           href="<?= base_url('member/forum/my-threads?status=locked') ?>">
            <i class="material-icons-outlined" style="font-size: 14px;">lock</i>
            Dikunci
        </a>
    </li>
</ul>

<!-- Thread List -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Daftar Thread</h5>
            </div>
            <div class="col-auto">
                <form action="<?= base_url('member/forum/my-threads') ?>" method="GET" class="d-flex gap-2">
                    <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php if (isset($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>"
                                        <?= (isset($_GET['category']) && $_GET['category'] == $category->id) ? 'selected' : '' ?>>
                                    <?= esc($category->name) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($threads)): ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">forum</i>
                <p class="mt-3 mb-0">Anda belum membuat thread</p>
                <small>Thread yang Anda buat akan muncul di sini</small>
                <br>
                <a href="<?= base_url('member/forum/thread/create') ?>" class="btn btn-primary mt-3">
                    <i class="material-icons-outlined">add</i>
                    Buat Thread Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($threads as $thread): ?>
                    <div class="list-group-item">
                        <div class="row align-items-start">
                            <div class="col">
                                <!-- Thread Title with Badges -->
                                <h6 class="mb-1">
                                    <a href="<?= base_url('member/forum/thread/' . $thread->id) ?>" class="text-decoration-none">
                                        <?= esc($thread->title) ?>
                                    </a>

                                    <?php if ($thread->is_pinned ?? false): ?>
                                        <span class="badge bg-info">
                                            <i class="material-icons-outlined" style="font-size: 12px;">push_pin</i>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($thread->is_locked ?? false): ?>
                                        <span class="badge bg-warning">
                                            <i class="material-icons-outlined" style="font-size: 12px;">lock</i>
                                        </span>
                                    <?php endif; ?>
                                </h6>

                                <!-- Category & Date -->
                                <div class="text-muted small mb-2">
                                    <span class="badge bg-secondary"><?= esc($thread->category_name ?? 'General') ?></span>
                                    <span class="mx-2">•</span>
                                    <i class="material-icons-outlined" style="font-size: 12px;">schedule</i>
                                    <?= date('d M Y, H:i', strtotime($thread->created_at)) ?>
                                    <?php if ($thread->updated_at && $thread->updated_at != $thread->created_at): ?>
                                        <span class="badge bg-info">Updated</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Thread Excerpt -->
                                <p class="text-muted small mb-2">
                                    <?= mb_substr(strip_tags($thread->content), 0, 150) ?>...
                                </p>

                                <!-- Stats -->
                                <div class="d-flex gap-3 text-muted small">
                                    <span>
                                        <i class="material-icons-outlined" style="font-size: 14px;">visibility</i>
                                        <?= number_format($thread->view_count ?? 0) ?> views
                                    </span>
                                    <span>
                                        <i class="material-icons-outlined" style="font-size: 14px;">forum</i>
                                        <?= number_format($thread->reply_count ?? 0) ?> replies
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="col-auto">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?= base_url('member/forum/thread/' . $thread->id) ?>"
                                       class="btn btn-outline-primary"
                                       data-bs-toggle="tooltip"
                                       title="Lihat Thread">
                                        <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                    </a>
                                    <a href="<?= base_url('member/forum/thread/edit/' . $thread->id) ?>"
                                       class="btn btn-outline-secondary"
                                       data-bs-toggle="tooltip"
                                       title="Edit Thread">
                                        <i class="material-icons-outlined" style="font-size: 16px;">edit</i>
                                    </a>
                                    <?php if (!($thread->is_locked ?? false)): ?>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?= $thread->id ?>"
                                                title="Hapus Thread">
                                            <i class="material-icons-outlined" style="font-size: 16px;">delete</i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteModal<?= $thread->id ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Hapus Thread</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus thread "<strong><?= esc($thread->title) ?></strong>"?</p>
                                    <div class="alert alert-warning">
                                        <i class="material-icons-outlined">warning</i>
                                        Thread dan semua balasannya akan dihapus secara permanen.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form action="<?= base_url('member/forum/thread/delete/' . $thread->id) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">Hapus Thread</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($threads) && isset($pager)): ?>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
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
