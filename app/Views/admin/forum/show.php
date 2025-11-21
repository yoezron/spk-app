<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="page-title"><?= esc($thread->title) ?></h1>
            <p class="text-muted">
                <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">category</i>
                <?= esc($thread->category_name) ?>
                <span class="mx-2">•</span>
                <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">person</i>
                <?= esc($thread->author_name ?? $thread->author_email) ?>
                <span class="mx-2">•</span>
                <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">access_time</i>
                <?= date('d M Y, H:i', strtotime($thread->created_at)) ?>
            </p>
        </div>
        <div class="col-auto ms-auto">
            <a href="<?= base_url('admin/forum') ?>" class="btn btn-secondary">
                <i class="material-icons-outlined">arrow_back</i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Thread Status Badges -->
<div class="row mb-3">
    <div class="col-12">
        <?php if ($thread->is_pinned): ?>
            <span class="badge bg-info">
                <i class="material-icons-outlined" style="font-size: 14px;">push_pin</i> Disematkan
            </span>
        <?php endif; ?>
        <?php if ($thread->is_locked): ?>
            <span class="badge bg-warning">
                <i class="material-icons-outlined" style="font-size: 14px;">lock</i> Dikunci
            </span>
        <?php endif; ?>
        <?php if ($thread->is_deleted): ?>
            <span class="badge bg-danger">
                <i class="material-icons-outlined" style="font-size: 14px;">delete</i> Terhapus
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- Moderation Actions -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Aksi Moderasi</h5>
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <!-- Pin/Unpin Thread -->
            <form action="<?= base_url('admin/forum/pin/' . $thread->id) ?>" method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-<?= $thread->is_pinned ? 'outline-secondary' : 'outline-info' ?>">
                    <i class="material-icons-outlined"><?= $thread->is_pinned ? 'push_pin' : 'push_pin' ?></i>
                    <?= $thread->is_pinned ? 'Unpin' : 'Pin' ?> Thread
                </button>
            </form>

            <!-- Lock/Unlock Thread -->
            <button type="button" class="btn btn-<?= $thread->is_locked ? 'outline-secondary' : 'outline-warning' ?>"
                    data-bs-toggle="modal" data-bs-target="#lockModal">
                <i class="material-icons-outlined"><?= $thread->is_locked ? 'lock_open' : 'lock' ?></i>
                <?= $thread->is_locked ? 'Buka Kunci' : 'Kunci' ?> Thread
            </button>

            <!-- Delete Thread -->
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteThreadModal">
                <i class="material-icons-outlined">delete</i>
                Hapus Thread
            </button>
        </div>
    </div>
</div>

<!-- Thread Content -->
<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <div class="avatar me-3">
                <?php if (!empty($thread->author_foto_path)): ?>
                    <img src="<?= base_url('uploads/profiles/' . $thread->author_foto_path) ?>" alt="Avatar" class="rounded-circle" width="40" height="40">
                <?php else: ?>
                    <div class="avatar-title bg-primary rounded-circle">
                        <?= strtoupper(substr($thread->author_name ?? $thread->author_email, 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <h6 class="mb-0"><?= esc($thread->author_name ?? $thread->author_email) ?></h6>
                <small class="text-muted"><?= date('d M Y, H:i', strtotime($thread->created_at)) ?></small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="thread-content">
            <?= nl2br(esc($thread->content)) ?>
        </div>

        <?php if ($thread->is_locked): ?>
            <div class="alert alert-warning mt-3 mb-0">
                <i class="material-icons-outlined">lock</i>
                <strong>Thread Dikunci:</strong> <?= esc($thread->lock_reason ?? 'Tidak ada alasan yang diberikan') ?>
            </div>
        <?php endif; ?>
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

<!-- Posts/Replies -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Balasan (<?= count($posts) ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($posts)): ?>
            <div class="p-4 text-center text-muted">
                <i class="material-icons-outlined" style="font-size: 48px;">forum</i>
                <p class="mt-2">Belum ada balasan</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($posts as $post): ?>
                    <div class="list-group-item <?= $post->is_deleted ? 'bg-light' : '' ?>">
                        <div class="row">
                            <div class="col-auto">
                                <div class="avatar">
                                    <?php if (!empty($post->foto_path)): ?>
                                        <img src="<?= base_url('uploads/profiles/' . $post->foto_path) ?>" alt="Avatar" class="rounded-circle" width="40" height="40">
                                    <?php else: ?>
                                        <div class="avatar-title bg-secondary rounded-circle" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($post->author_name ?? $post->author_email, 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-0"><?= esc($post->author_name ?? $post->author_email) ?></h6>
                                        <small class="text-muted">
                                            <?= date('d M Y, H:i', strtotime($post->created_at)) ?>
                                            <?php if ($post->updated_at && $post->updated_at != $post->created_at): ?>
                                                <span class="badge bg-secondary">Edited</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php if (!$post->is_deleted): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePostModal<?= $post->id ?>">
                                            <i class="material-icons-outlined" style="font-size: 16px;">delete</i>
                                            Hapus
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Terhapus</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 post-content">
                                    <?php if ($post->is_deleted): ?>
                                        <p class="text-muted fst-italic">
                                            <i class="material-icons-outlined" style="font-size: 14px;">block</i>
                                            Post ini telah dihapus oleh moderator.
                                            <br><strong>Alasan:</strong> <?= esc($post->delete_reason ?? 'Tidak ada alasan yang diberikan') ?>
                                        </p>
                                    <?php else: ?>
                                        <?= nl2br(esc($post->content)) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Post Modal -->
                    <?php if (!$post->is_deleted): ?>
                        <div class="modal fade" id="deletePostModal<?= $post->id ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?= base_url('admin/forum/post/delete/' . $post->id) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Hapus Post</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Anda yakin ingin menghapus post ini?</p>
                                            <div class="mb-3">
                                                <label for="reason" class="form-label">Alasan Penghapusan</label>
                                                <textarea name="reason" class="form-control" rows="3" required>Konten melanggar aturan forum</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Hapus Post</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lock Thread Modal -->
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/forum/lock/' . $thread->id) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= $thread->is_locked ? 'Buka Kunci' : 'Kunci' ?> Thread</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (!$thread->is_locked): ?>
                        <p>Thread yang dikunci tidak dapat menerima balasan baru. Berikan alasan untuk mengunci thread:</p>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Alasan Penguncian</label>
                            <textarea name="reason" class="form-control" rows="3" required>Diskusi sudah selesai / Melanggar aturan forum</textarea>
                        </div>
                    <?php else: ?>
                        <p>Thread akan dibuka kembali dan dapat menerima balasan baru.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-<?= $thread->is_locked ? 'primary' : 'warning' ?>">
                        <?= $thread->is_locked ? 'Buka Kunci' : 'Kunci' ?> Thread
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Thread Modal -->
<div class="modal fade" id="deleteThreadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/forum/thread/delete/' . $thread->id) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Hapus Thread</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="material-icons-outlined">warning</i>
                        <strong>Peringatan!</strong> Tindakan ini akan menghapus thread beserta semua balasannya (<?= count($posts) ?> balasan).
                    </div>
                    <p>Apakah Anda yakin ingin menghapus thread "<strong><?= esc($thread->title) ?></strong>"?</p>
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Alasan Penghapusan</label>
                        <textarea name="reason" class="form-control" rows="3" required>Konten melanggar aturan forum</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Thread</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
