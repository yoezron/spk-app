<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><?= $title ?></h1>
            <p class="text-muted">Konten forum yang telah dihapus oleh moderator</p>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('admin/forum') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali ke Forum
            </a>
        </div>
    </div>
</div>

<!-- Deleted Threads List -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Thread Terhapus</h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-danger"><?= count($deleted_threads) ?> Thread</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($deleted_threads)): ?>
            <div class="p-5 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 64px;">delete_sweep</i>
                <p class="mt-3 mb-0">Tidak ada thread yang dihapus</p>
                <small>Thread yang dihapus oleh moderator akan muncul di sini</small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Thread</th>
                            <th>Kategori</th>
                            <th>Penulis</th>
                            <th>Dihapus Oleh</th>
                            <th>Tanggal Dihapus</th>
                            <th>Alasan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deleted_threads as $thread): ?>
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-danger">
                                            <i class="material-icons-outlined" style="font-size: 14px;">delete</i>
                                            <?= esc($thread->title) ?>
                                        </strong>
                                        <small class="text-muted">
                                            <?= mb_substr(strip_tags($thread->content), 0, 100) ?>...
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= esc($thread->category_name) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span><?= esc($thread->author_name ?? $thread->author_email) ?></span>
                                        <small class="text-muted"><?= esc($thread->author_email) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?= esc($thread->deleted_by_email ?? 'Unknown') ?>
                                </td>
                                <td>
                                    <span data-bs-toggle="tooltip" title="<?= date('d F Y, H:i:s', strtotime($thread->deleted_at)) ?>">
                                        <?= date('d M Y', strtotime($thread->deleted_at)) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= esc($thread->delete_reason ?? 'Tidak ada alasan') ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- View Thread Details -->
                                        <button type="button" class="btn btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewThreadModal<?= $thread->id ?>">
                                            <i class="material-icons-outlined" style="font-size: 16px;">visibility</i>
                                        </button>

                                        <!-- Restore Thread -->
                                        <form action="<?= base_url('admin/forum/restore/' . $thread->id) ?>"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Pulihkan thread ini?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-success">
                                                <i class="material-icons-outlined" style="font-size: 16px;">restore</i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Thread Modal -->
                            <div class="modal fade" id="viewThreadModal<?= $thread->id ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="material-icons-outlined">delete</i>
                                                <?= esc($thread->title) ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Thread Info -->
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Kategori:</strong> <?= esc($thread->category_name) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Penulis:</strong> <?= esc($thread->author_name ?? $thread->author_email) ?>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <strong>Dibuat:</strong> <?= date('d M Y, H:i', strtotime($thread->created_at)) ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Dihapus:</strong> <?= date('d M Y, H:i', strtotime($thread->deleted_at)) ?>
                                                </div>
                                            </div>

                                            <!-- Deletion Info -->
                                            <div class="alert alert-danger">
                                                <strong>Dihapus oleh:</strong> <?= esc($thread->deleted_by_email ?? 'Unknown') ?>
                                                <br>
                                                <strong>Alasan:</strong> <?= esc($thread->delete_reason ?? 'Tidak ada alasan yang diberikan') ?>
                                            </div>

                                            <!-- Thread Content -->
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="mb-0">Konten Thread</h6>
                                                </div>
                                                <div class="card-body">
                                                    <?= nl2br(esc($thread->content)) ?>
                                                </div>
                                                <div class="card-footer text-muted">
                                                    <div class="row">
                                                        <div class="col">
                                                            <i class="material-icons-outlined" style="font-size: 14px;">visibility</i>
                                                            <?= number_format($thread->view_count ?? 0) ?> views
                                                        </div>
                                                        <div class="col text-end">
                                                            <i class="material-icons-outlined" style="font-size: 14px;">forum</i>
                                                            <?= number_format($thread->post_count ?? 0) ?> replies
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            <form action="<?= base_url('admin/forum/restore/' . $thread->id) ?>"
                                                  method="POST"
                                                  class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="material-icons-outlined">restore</i>
                                                    Pulihkan Thread
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($deleted_threads) && $pager): ?>
        <div class="card-footer">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<!-- Info Box -->
<div class="alert alert-info mt-3">
    <i class="material-icons-outlined">info</i>
    <strong>Informasi:</strong> Thread yang dihapus dapat dipulihkan kembali. Gunakan tombol "Restore" untuk mengembalikan thread beserta semua balasannya.
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
